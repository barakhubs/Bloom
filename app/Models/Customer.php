<?php

namespace App\Models;

use App\Core\Model;

/** Optional public shopper/donor accounts (separate from admin_users). */
class Customer extends Model
{
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /** A verified account for this email, if any (used to link guest purchases). */
    public function findVerifiedByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE email = ? AND email_verified_at IS NOT NULL');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare('INSERT INTO customers (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $passwordHash]);

        return (int) $this->db->lastInsertId();
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE customers SET last_login_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function markVerified(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE customers SET email_verified_at = NOW() WHERE id = ? AND email_verified_at IS NULL');
        $stmt->execute([$id]);
    }

    /**
     * Attaches past guest orders/donations made with this (verified) email.
     * Only ever called after the customer has proven they own the address.
     */
    public function linkGuestHistory(int $id, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE orders SET customer_id = ? WHERE customer_id IS NULL AND customer_email = ?');
        $stmt->execute([$id, $email]);

        $stmt = $this->db->prepare('UPDATE donations SET customer_id = ? WHERE customer_id IS NULL AND donor_email = ?');
        $stmt->execute([$id, $email]);
    }

    /** @param array{name: string, phone: ?string, ship_line1: ?string, ship_line2: ?string, ship_city: ?string, ship_state: ?string, ship_postal_code: ?string, ship_country: ?string} $data */
    public function updateProfile(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE customers
             SET name = ?, phone = ?, ship_line1 = ?, ship_line2 = ?, ship_city = ?, ship_state = ?, ship_postal_code = ?, ship_country = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['name'], $data['phone'], $data['ship_line1'], $data['ship_line2'], $data['ship_city'],
            $data['ship_state'], $data['ship_postal_code'], $data['ship_country'], $id,
        ]);
    }

    /** New email starts unverified again. */
    public function changeEmail(int $id, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE customers SET email = ?, email_verified_at = NULL WHERE id = ?');
        $stmt->execute([$email, $id]);
    }

    public function setPassword(int $id, string $passwordHash): void
    {
        $stmt = $this->db->prepare('UPDATE customers SET password_hash = ? WHERE id = ?');
        $stmt->execute([$passwordHash, $id]);
    }

    public function setStripeCustomerId(int $id, ?string $stripeCustomerId): void
    {
        $stmt = $this->db->prepare('UPDATE customers SET stripe_customer_id = ? WHERE id = ?');
        $stmt->execute([$stripeCustomerId, $id]);
    }

    /** Orders and donations are kept (customer_id SET NULL); tokens and wishlist cascade. */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM customers WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** Admin list with order/donation counts; $search matches name or email. */
    public function list(string $search): array
    {
        $sql = 'SELECT c.*,
                    (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id AND o.status IN (\'paid\', \'shipped\')) AS order_count,
                    (SELECT COUNT(*) FROM donations d WHERE d.customer_id = c.id AND d.status = \'paid\') AS donation_count
                FROM customers c'
            . ($search !== '' ? ' WHERE c.name LIKE ? OR c.email LIKE ?' : '')
            . ' ORDER BY c.created_at DESC LIMIT 500';

        $stmt = $this->db->prepare($sql);
        $like = '%' . addcslashes($search, '%_\\') . '%';
        $stmt->execute($search !== '' ? [$like, $like] : []);

        return $stmt->fetchAll();
    }
}
