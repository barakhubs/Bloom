<?php

namespace App\Models;

use App\Core\Model;

/**
 * Guest shop orders. Created 'pending' (with snapshot line items) right before
 * the Stripe Checkout redirect; only the verified webhook marks them 'paid'.
 */
class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'shipped', 'cancelled', 'expired'];

    /** Unambiguous characters for order references (no 0/O, 1/I/L). */
    private const REFERENCE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /**
     * Inserts a pending order + its items in one transaction and returns the order row.
     *
     * @param array<int, array{product_id: int, name: string, unit_price_cents: int, quantity: int}> $items
     */
    public function createPending(array $items, int $shippingCents, string $currency): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['unit_price_cents'] * $item['quantity'];
        }

        $this->db->beginTransaction();

        try {
            $orderStmt = $this->db->prepare(
                'INSERT INTO orders (reference, subtotal_cents, shipping_cents, total_cents, currency) VALUES (?, ?, ?, ?, ?)'
            );

            // A reference collision is astronomically unlikely, but retry rather than fail the checkout.
            for ($attempt = 0; ; $attempt++) {
                $reference = self::newReference();
                try {
                    $orderStmt->execute([$reference, $subtotal, $shippingCents, $subtotal + $shippingCents, $currency]);
                    break;
                } catch (\PDOException $e) {
                    if ($attempt >= 4 || $e->getCode() !== '23000') {
                        throw $e;
                    }
                }
            }

            $orderId = (int) $this->db->lastInsertId();
            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, unit_price_cents, quantity) VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([$orderId, $item['product_id'], $item['name'], $item['unit_price_cents'], $item['quantity']]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->find($orderId);
    }

    public function attachSession(int $id, string $sessionId): void
    {
        $stmt = $this->db->prepare('UPDATE orders SET stripe_session_id = ? WHERE id = ?');
        $stmt->execute([$sessionId, $id]);
    }

    /** Removes a pending order whose Checkout Session could not be created (items cascade). */
    public function deletePending(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
    }

    /**
     * pending -> paid with the customer + shipping details Stripe collected.
     * True only for the request that made the change (idempotent webhook).
     *
     * @param array{name: ?string, email: ?string, phone: ?string, ship_name: ?string, line1: ?string, line2: ?string, city: ?string, state: ?string, postal_code: ?string, country: ?string} $details
     */
    public function markPaidBySession(string $sessionId, ?string $paymentIntentId, array $details): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET status = 'paid', paid_at = NOW(), stripe_payment_intent_id = ?,
                 customer_name = ?, customer_email = ?, customer_phone = ?,
                 ship_name = ?, ship_line1 = ?, ship_line2 = ?, ship_city = ?, ship_state = ?, ship_postal_code = ?, ship_country = ?
             WHERE stripe_session_id = ? AND status = 'pending'"
        );
        $stmt->execute([
            $paymentIntentId,
            $details['name'], $details['email'], $details['phone'],
            $details['ship_name'], $details['line1'], $details['line2'], $details['city'],
            $details['state'], $details['postal_code'], $details['country'],
            $sessionId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function markExpiredBySession(string $sessionId): void
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = 'expired' WHERE stripe_session_id = ? AND status = 'pending'");
        $stmt->execute([$sessionId]);
    }

    /** paid -> shipped. True if the status changed. */
    public function markShipped(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = 'shipped', shipped_at = NOW() WHERE id = ? AND status = 'paid'");
        $stmt->execute([$id]);

        return $stmt->rowCount() === 1;
    }

    /** paid/shipped -> cancelled (after a refund in Stripe). True if the status changed. */
    public function markCancelled(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND status IN ('paid', 'shipped')");
        $stmt->execute([$id]);

        return $stmt->rowCount() === 1;
    }

    /** Order row plus an 'items' list. */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $this->withItems($row) : null;
    }

    public function findBySession(string $sessionId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE stripe_session_id = ?');
        $stmt->execute([$sessionId]);
        $row = $stmt->fetch();

        return $row !== false ? $this->withItems($row) : null;
    }

    /** Admin list, newest 500 first, with an item count. */
    public function list(?string $status): array
    {
        $sql = 'SELECT o.*, (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE order_id = o.id) AS item_count
                FROM orders o'
            . ($status !== null ? ' WHERE o.status = ?' : '')
            . ' ORDER BY o.created_at DESC, o.id DESC LIMIT 500';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($status !== null ? [$status] : []);

        return $stmt->fetchAll();
    }

    private function withItems(array $order): array
    {
        $stmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
        $stmt->execute([(int) $order['id']]);
        $order['items'] = $stmt->fetchAll();

        return $order;
    }

    private static function newReference(): string
    {
        $alphabet = self::REFERENCE_ALPHABET;
        $reference = 'BBB-';

        for ($i = 0; $i < 6; $i++) {
            $reference .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $reference;
    }
}
