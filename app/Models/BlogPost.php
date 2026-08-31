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
}
