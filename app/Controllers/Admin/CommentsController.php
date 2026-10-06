<?php

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Csrf;
use App\Models\BlogComment;

class CommentsController extends AdminController
{
    protected ?string $permission = 'comments';

    public function index(): void
    {
        $this->view('admin/blog/comments', [
            'title' => 'Comments - Bloom Beyond Borders Admin',
            'comments' => (new BlogComment())->allWithPostTitles(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function approve(string $id): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            (new BlogComment())->approve((int) $id);
        }

        header('Location: /admin/comments');
        exit;
    }

    public function destroy(string $id): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            (new BlogComment())->delete((int) $id);
        }

        header('Location: /admin/comments');
        exit;
    }
}
