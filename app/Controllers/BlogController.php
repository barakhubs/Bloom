<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index(): void
    {
        $this->view('blog/index', [
            'title' => 'Blog - Bloom Beyond Borders',
            'pageTitle' => 'Blog',
            'posts' => (new BlogPost())->publishedList(),
        ]);
    }

    public function show(string $slug): void
    {
        $post = (new BlogPost())->findPublishedBySlug($slug);

        if ($post === null) {
            (new ErrorController())->notFound();

            return;
        }

        $this->view('blog/show', [
            'title' => $post['title'] . ' - Bloom Beyond Borders',
            'pageTitle' => $post['title'],
            'post' => $post,
        ]);
    }
}
