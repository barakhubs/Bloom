<?php

namespace App\Models;

use App\Core\Model;

class AdminUser extends Model
{
    private const SELECT_WITH_ROLE = 'SELECT u.*, r.name AS role_name, r.is_system
        FROM admin_users u
        JOIN roles r ON r.id = u.role_id';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(self::SELECT_WITH_ROLE . ' WHERE u.email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(self::SELECT_WITH_ROLE . ' WHERE u.id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    public function all(): array
    {
        return $this->db->query(self::SELECT_WITH_ROLE . ' ORDER BY u.name ASC, u.email ASC')->fetchAll();
    }

    public function emailTaken(string $email, ?int $excludeId = null): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM admin_users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $excludeId ?? 0]);

        return $stmt->fetch() !== false;
    }

    /** New users start as 'invited' with no password until they accept their invite. */
    public function create(string $name, string $email, int $roleId): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO admin_users (name, email, password_hash, role_id, status) VALUES (?, ?, NULL, ?, 'invited')"
        );
        $stmt->execute([$name, $email, $roleId]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $email, int $roleId, string $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_users SET name = ?, email = ?, role_id = ?, status = ? WHERE id = ?'
        );
        $stmt->execute([$name, $email, $roleId, $status, $id]);
    }

    public function updateName(int $id, string $name): void
    {
        $stmt = $this->db->prepare('UPDATE admin_users SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }

    /** Sets the password, and activates the account if it was still an open invite. */
    public function setPassword(int $id, string $passwordHash): void
    {
        $stmt = $this->db->prepare(
            "UPDATE admin_users
             SET password_hash = ?, status = CASE WHEN status = 'invited' THEN 'active' ELSE status END
             WHERE id = ?"
        );
        $stmt->execute([$passwordHash, $id]);
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM admin_users WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function countActiveSuperAdmins(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM admin_users u JOIN roles r ON r.id = u.role_id
             WHERE r.is_system = 1 AND u.status = 'active'"
        )->fetchColumn();
    }
}
