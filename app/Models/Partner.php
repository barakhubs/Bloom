<?php

namespace App\Models;

use App\Core\Model;

class Partner extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM partners ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM partners WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function create(string $name, ?string $logoPath, ?string $tagline, ?string $description, ?string $linkUrl, int $sortOrder): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO partners (name, logo_path, tagline, description, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $logoPath, $tagline, $description, $linkUrl, $sortOrder]);
    }

    public function update(int $id, string $name, ?string $logoPath, ?string $tagline, ?string $description, ?string $linkUrl, int $sortOrder): void
    {
        if ($logoPath !== null) {
            $stmt = $this->db->prepare(
                'UPDATE partners SET name = ?, logo_path = ?, tagline = ?, description = ?, link_url = ?, sort_order = ? WHERE id = ?'
            );
            $stmt->execute([$name, $logoPath, $tagline, $description, $linkUrl, $sortOrder, $id]);

            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE partners SET name = ?, tagline = ?, description = ?, link_url = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->execute([$name, $tagline, $description, $linkUrl, $sortOrder, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM partners WHERE id = ?');
        $stmt->execute([$id]);
    }
}
