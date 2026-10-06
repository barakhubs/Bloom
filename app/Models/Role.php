<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Role extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT r.*, (SELECT COUNT(*) FROM admin_users u WHERE u.role_id = r.id) AS user_count
             FROM roles r
             ORDER BY r.is_system DESC, r.name ASC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function nameTaken(string $name, ?int $excludeId = null): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE name = ? AND id != ?');
        $stmt->execute([$name, $excludeId ?? 0]);

        return $stmt->fetch() !== false;
    }

    /** @return string[] permission slugs granted to the role */
    public function permissions(int $roleId): array
    {
        $stmt = $this->db->prepare('SELECT permission FROM role_permissions WHERE role_id = ?');
        $stmt->execute([$roleId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** @param string[] $permissions */
    public function create(string $name, array $permissions): int
    {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('INSERT INTO roles (name, is_system) VALUES (?, 0)');
        $stmt->execute([$name]);
        $id = (int) $this->db->lastInsertId();
        $this->replacePermissions($id, $permissions);
        $this->db->commit();

        return $id;
    }

    /** System roles are never changed - the WHERE clause enforces it even if a caller forgets. */
    public function update(int $id, string $name, array $permissions): void
    {
        $role = $this->find($id);
        if ($role === null || !empty($role['is_system'])) {
            return;
        }

        $this->db->beginTransaction();
        $stmt = $this->db->prepare('UPDATE roles SET name = ? WHERE id = ? AND is_system = 0');
        $stmt->execute([$name, $id]);
        $this->replacePermissions($id, $permissions);
        $this->db->commit();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM roles WHERE id = ? AND is_system = 0');
        $stmt->execute([$id]);
    }

    public function userCount(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM admin_users WHERE role_id = ?');
        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn();
    }

    private function replacePermissions(int $roleId, array $permissions): void
    {
        $this->db->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$roleId]);
        $stmt = $this->db->prepare('INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)');
        foreach ($permissions as $permission) {
            $stmt->execute([$roleId, $permission]);
        }
    }
}
