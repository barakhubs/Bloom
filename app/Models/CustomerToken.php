<?php

namespace App\Models;

use App\Core\Model;

/**
 * Single-use email-verification / password-reset tokens for customer
 * accounts. Same rules as AdminUserToken: only the SHA-256 hash is stored,
 * the raw token only exists in the emailed link.
 */
class CustomerToken extends Model
{
    public const TTL_HOURS = ['verify' => 48, 'reset' => 2];

    /** Issues a fresh token (voiding any earlier unused one of the same purpose) and returns the raw value. */
    public function issue(int $customerId, string $purpose): string
    {
        $token = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare(
            'UPDATE customer_tokens SET used_at = NOW() WHERE customer_id = ? AND purpose = ? AND used_at IS NULL'
        );
        $stmt->execute([$customerId, $purpose]);

        $stmt = $this->db->prepare(
            'INSERT INTO customer_tokens (customer_id, token_hash, purpose, expires_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))'
        );
        $stmt->execute([$customerId, hash('sha256', $token), $purpose, self::TTL_HOURS[$purpose]]);

        return $token;
    }

    /** The token row (id, customer_id) if it's unused, unexpired, and for $purpose; not consumed. */
    public function findValid(string $token, string $purpose): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, customer_id FROM customer_tokens
             WHERE token_hash = ? AND purpose = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([hash('sha256', $token), $purpose]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Voids every outstanding token for the customer. Called when their email
     * changes: links sent to the old address must not verify the new one.
     */
    public function voidAll(int $customerId): void
    {
        $stmt = $this->db->prepare('UPDATE customer_tokens SET used_at = NOW() WHERE customer_id = ? AND used_at IS NULL');
        $stmt->execute([$customerId]);
    }

    /** Marks the token used; false if something else already consumed it. */
    public function consume(int $tokenId): bool
    {
        $stmt = $this->db->prepare('UPDATE customer_tokens SET used_at = NOW() WHERE id = ? AND used_at IS NULL');
        $stmt->execute([$tokenId]);

        return $stmt->rowCount() === 1;
    }
}
