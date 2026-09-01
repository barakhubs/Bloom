<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TeamMember extends Model
{
    public function latest(int $limit = 3): array
    {
        $stmt = $this->db->prepare('SELECT * FROM team_members ORDER BY sort_order ASC, id ASC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM team_members ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM team_members WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function create(string $name, string $title, string $bio, ?string $photoPath, int $sortOrder): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO team_members (name, title, bio, photo_path, sort_order) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $title, $bio, $photoPath, $sortOrder]);
    }

    public function update(int $id, string $name, string $title, string $bio, ?string $photoPath, int $sortOrder): void
    {
        if ($photoPath !== null) {
            $stmt = $this->db->prepare(
                'UPDATE team_members SET name = ?, title = ?, bio = ?, photo_path = ?, sort_order = ? WHERE id = ?'
            );
            $stmt->execute([$name, $title, $bio, $photoPath, $sortOrder, $id]);

            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE team_members SET name = ?, title = ?, bio = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->execute([$name, $title, $bio, $sortOrder, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM team_members WHERE id = ?');
        $stmt->execute([$id]);
    }
}
