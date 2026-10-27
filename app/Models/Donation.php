<?php

namespace App\Models;

use App\Core\Model;

/**
 * One-time Stripe donations. Rows start 'pending' when the Checkout Session is
 * opened; only the verified webhook moves them to 'paid' (or 'expired').
 */
class Donation extends Model
{
    public const STATUSES = ['pending', 'paid', 'expired', 'failed'];

    /** Inserts a pending donation and returns its id. */
    public function createPending(?int $schemeId, int $amountCents, string $currency, ?string $message): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO donations (scheme_id, amount_cents, currency, message) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$schemeId, $amountCents, $currency, $message]);

        return (int) $this->db->lastInsertId();
    }

    public function attachSession(int $id, string $sessionId): void
    {
        $stmt = $this->db->prepare('UPDATE donations SET stripe_session_id = ? WHERE id = ?');
        $stmt->execute([$sessionId, $id]);
    }

    /** Removes a pending row whose Checkout Session could not be created. */
    public function deletePending(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM donations WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
    }

    /**
     * pending -> paid, recording the donor details Stripe collected. Returns true
     * only for the request that actually made the change, so a retried or
     * replayed webhook can't send a second receipt.
     */
    public function markPaidBySession(string $sessionId, ?string $paymentIntentId, ?string $donorName, ?string $donorEmail): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE donations
             SET status = 'paid', paid_at = NOW(), stripe_payment_intent_id = ?, donor_name = ?, donor_email = ?
             WHERE stripe_session_id = ? AND status = 'pending'"
        );
        $stmt->execute([$paymentIntentId, $donorName, $donorEmail, $sessionId]);

        return $stmt->rowCount() === 1;
    }

    public function markStatusBySession(string $sessionId, string $status): void
    {
        $stmt = $this->db->prepare("UPDATE donations SET status = ? WHERE stripe_session_id = ? AND status = 'pending'");
        $stmt->execute([$status, $sessionId]);
    }

    /** The donation (with scheme_name) for a Checkout Session. */
    public function findBySession(string $sessionId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT d.*, s.name AS scheme_name FROM donations d
             LEFT JOIN donation_schemes s ON s.id = d.scheme_id
             WHERE d.stripe_session_id = ?'
        );
        $stmt->execute([$sessionId]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Admin list, newest 500 first. $schemeId: null = all, 0 = general (no scheme).
     */
    public function list(?string $status, ?int $schemeId): array
    {
        $where = [];
        $params = [];

        if ($status !== null) {
            $where[] = 'd.status = ?';
            $params[] = $status;
        }
        if ($schemeId === 0) {
            $where[] = 'd.scheme_id IS NULL';
        } elseif ($schemeId !== null) {
            $where[] = 'd.scheme_id = ?';
            $params[] = $schemeId;
        }

        $sql = 'SELECT d.*, s.name AS scheme_name FROM donations d
                LEFT JOIN donation_schemes s ON s.id = d.scheme_id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY d.created_at DESC, d.id DESC LIMIT 500';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** Paid totals: ['count' => int, 'cents' => int]. */
    public function paidTotals(): array
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS count, COALESCE(SUM(amount_cents), 0) AS cents FROM donations WHERE status = 'paid'"
        )->fetch();

        return ['count' => (int) $row['count'], 'cents' => (int) $row['cents']];
    }
}
