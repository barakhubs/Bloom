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
            'albums' => (new GalleryAlbum())->allWithImages(),
        ]);
    }
}
