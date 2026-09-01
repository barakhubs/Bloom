<?php

namespace App\Models;

use App\Core\Model;

class BlogPost extends Model
{
    public function publishedList(): array
    {
        return $this->db
            ->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC")
            ->fetchAll();
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        return $post !== false ? $post : null;
    }

    /**
     * All posts regardless of status, for the admin list.
     */
    public function all(): array
    {
        return $this->db->query('SELECT * FROM blog_posts ORDER BY created_at DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM blog_posts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function create(
        string $title,
        string $slug,
        string $body,
        ?string $featuredImagePath,
        string $status,
        ?string $publishedAt
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO blog_posts (title, slug, body, featured_image_path, status, published_at) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$title, $slug, $body, $featuredImagePath, $status, $publishedAt]);
    }

    public function update(
        int $id,
        string $title,
        string $slug,
        string $body,
        ?string $featuredImagePath,
        string $status,
        ?string $publishedAt
    ): void {
        if ($featuredImagePath !== null) {
            $stmt = $this->db->prepare(
                'UPDATE blog_posts SET title = ?, slug = ?, body = ?, featured_image_path = ?, status = ?, published_at = ? WHERE id = ?'
            );
            $stmt->execute([$title, $slug, $body, $featuredImagePath, $status, $publishedAt, $id]);

            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE blog_posts SET title = ?, slug = ?, body = ?, status = ?, published_at = ? WHERE id = ?'
        );
        $stmt->execute([$title, $slug, $body, $status, $publishedAt, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM blog_posts WHERE id = ?');
        $stmt->execute([$id]);
    }
}
