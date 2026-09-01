<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class GalleryImage extends Model
{
    public function latest(int $limit = 6): array
    {
        $stmt = $this->db->prepare('SELECT * FROM gallery_images ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function forAlbum(int $albumId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM gallery_images WHERE album_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$albumId]);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM gallery_images WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function create(int $albumId, string $imagePath, ?string $caption, int $sortOrder): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO gallery_images (album_id, image_path, caption, sort_order) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$albumId, $imagePath, $caption, $sortOrder]);
    }

    public function update(int $id, ?string $caption, int $sortOrder): void
    {
        $stmt = $this->db->prepare('UPDATE gallery_images SET caption = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$caption, $sortOrder, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM gallery_images WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function deleteForAlbum(int $albumId): void
    {
        $stmt = $this->db->prepare('DELETE FROM gallery_images WHERE album_id = ?');
        $stmt->execute([$albumId]);
    }
}
