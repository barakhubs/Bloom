<?php

namespace App\Models;

use App\Core\Model;

/**
 * Single-use invite / password-reset tokens. The raw token only ever exists in
 * the emailed link; the DB stores its SHA-256 hash.
 */
class AdminUserToken extends Model
{
    public const TTL_HOURS = ['invite' => 72, 'reset' => 2];

    /** Issues a fresh token (voiding any earlier unused one of the same purpose) and returns the raw value. */
    public function issue(int $userId, string $purpose): string
    {
        $token = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare(
            'UPDATE admin_user_tokens SET used_at = NOW() WHERE user_id = ? AND purpose = ? AND used_at IS NULL'
        );
        $stmt->execute([$userId, $purpose]);

        $stmt = $this->db->prepare(
            'INSERT INTO admin_user_tokens (user_id, token_hash, purpose, expires_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))'
        );
        $stmt->execute([$userId, hash('sha256', $token), $purpose, self::TTL_HOURS[$purpose]]);

        return $token;
    }

    /** Returns the token row (id, user_id, purpose) if it's unused and unexpired, without consuming it. */
    public function findValid(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_id, purpose FROM admin_user_tokens
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([hash('sha256', $token)]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /** Marks the token used; false if something else already consumed it. */
    public function consume(int $tokenId): bool
    {
        $stmt = $this->db->prepare('UPDATE admin_user_tokens SET used_at = NOW() WHERE id = ? AND used_at IS NULL');
        $stmt->execute([$tokenId]);

        return $stmt->rowCount() === 1;
    }
}
