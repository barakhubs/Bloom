<?php

namespace App\Models;

use App\Core\Model;

class BlogComment extends Model
{
    public function approvedForPost(int $postId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM blog_comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC"
        );
        $stmt->execute([$postId]);

        return $stmt->fetchAll();
    }

    public function create(int $postId, string $authorName, string $authorEmail, ?string $website, string $body): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO blog_comments (post_id, author_name, author_email, website, body) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$postId, $authorName, $authorEmail, $website, $body]);
    }

    /**
     * Every comment across every post, joined with the post title/slug, for the
     * admin moderation queue - pending ones first, then newest first.
     */
    public function allWithPostTitles(): array
    {
        return $this->db->query(
            "SELECT c.*, p.title AS post_title, p.slug AS post_slug
             FROM blog_comments c
             JOIN blog_posts p ON p.id = c.post_id
             ORDER BY (c.status = 'pending') DESC, c.created_at DESC"
        )->fetchAll();
    }

    public function approve(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE blog_comments SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM blog_comments WHERE id = ?');
        $stmt->execute([$id]);
    }
}
