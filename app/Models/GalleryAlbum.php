<?php

namespace App\Models;

use App\Core\Model;

class GalleryAlbum extends Model
{
    /**
     * All albums, each with its images nested under the 'images' key.
     */
    public function allWithImages(): array
    {
        $albums = $this->all();

        if (empty($albums)) {
            return [];
        }

        $imagesStmt = $this->db->prepare(
            'SELECT * FROM gallery_images WHERE album_id = ? ORDER BY sort_order ASC, id ASC'
        );

        foreach ($albums as &$album) {
            $imagesStmt->execute([$album['id']]);
            $album['images'] = $imagesStmt->fetchAll();
        }

        return $albums;
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM gallery_albums ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    /**
     * All albums with a count of how many images each contains.
     */
    public function allWithImageCounts(): array
    {
        return $this->db->query(
            'SELECT a.*, COUNT(i.id) AS image_count
             FROM gallery_albums a
             LEFT JOIN gallery_images i ON i.album_id = a.id
             GROUP BY a.id
             ORDER BY a.sort_order ASC, a.id ASC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM gallery_albums WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function create(string $name, string $slug, int $sortOrder): int
    {
        $stmt = $this->db->prepare('INSERT INTO gallery_albums (name, slug, sort_order) VALUES (?, ?, ?)');
        $stmt->execute([$name, $slug, $sortOrder]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $slug, int $sortOrder): void
    {
        $stmt = $this->db->prepare('UPDATE gallery_albums SET name = ?, slug = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$name, $slug, $sortOrder, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM gallery_albums WHERE id = ?');
        $stmt->execute([$id]);
    }
}
