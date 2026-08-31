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
}
