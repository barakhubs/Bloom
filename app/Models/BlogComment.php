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
}
