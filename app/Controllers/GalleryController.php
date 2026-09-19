<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\GalleryAlbum;

class GalleryController extends Controller
{
    public function index(): void
    {
        $this->view('gallery/index', [
            'title' => 'Gallery - Bloom Beyond Borders',
            'pageTitle' => 'Gallery',
            'description' => 'Photos from Bloom Beyond Borders\' work supporting children and women in Uganda and African immigrant families in the USA.',
            'albums' => (new GalleryAlbum())->allWithImages(),
        ]);
    }
}
