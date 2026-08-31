<?php

namespace App\Controllers;

use App\Core\Controller;

class BlogController extends Controller
{
    public function index(): void
    {
        $this->view('blog/index', [
            'title' => 'Blog - Bloom Beyond Borders',
            'pageTitle' => 'Blog',
        ]);
    }

    public function show(string $slug): void
    {
        $this->view('blog/show', [
            'title' => 'Blog - Bloom Beyond Borders',
            'pageTitle' => 'Blog Post',
            'slug' => $slug,
        ]);
    }
}
