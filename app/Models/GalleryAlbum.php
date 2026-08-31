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
        $albums = $this->db
            ->query('SELECT * FROM gallery_albums ORDER BY sort_order ASC, id ASC')
            ->fetchAll();

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
}
