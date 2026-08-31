<?php

namespace App\Controllers;

use App\Core\Controller;

class GalleryController extends Controller
{
    public function index(): void
    {
        $this->view('gallery/index', [
            'title' => 'Gallery - Bloom Beyond Borders',
            'pageTitle' => 'Gallery',
        ]);
    }
}
