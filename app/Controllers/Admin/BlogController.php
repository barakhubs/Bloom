<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Slug;
use App\Core\Upload;
use App\Models\BlogComment;
use App\Models\BlogPost;
use RuntimeException;

class BlogController extends AdminController
{
    public function index(): void
    {
        $this->view('admin/blog/index', [
            'title' => 'Blog - Bloom Beyond Borders Admin',
            'posts' => (new BlogPost())->all(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $errors = $_SESSION['post_errors'] ?? [];
        unset($_SESSION['post_errors']);

        $this->view('admin/blog/form', [
            'title' => 'Add Post - Bloom Beyond Borders Admin',
            'post' => null,
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $post = (new BlogPost())->find((int) $id);

        if ($post === null) {
            (new ErrorController())->notFound();

            return;
        }

        $errors = $_SESSION['post_errors'] ?? [];
        unset($_SESSION['post_errors']);

        $this->view('admin/blog/form', [
            'title' => 'Edit Post - Bloom Beyond Borders Admin',
            'post' => $post,
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/blog');
            exit;
        }

        $model = new BlogPost();
        $post = $model->find((int) $id);

        if ($post !== null) {
            // blog_comments rows for this post cascade automatically via
            // ON DELETE CASCADE on blog_comments.post_id.
            $model->delete((int) $id);
            if (!empty($post['featured_image_path'])) {
                Upload::delete($post['featured_image_path']);
            }
        }

        header('Location: /admin/blog');
        exit;
    }

    public function commentsIndex(): void
    {
        $this->view('admin/blog/comments', [
            'title' => 'Comments - Bloom Beyond Borders Admin',
            'comments' => (new BlogComment())->allWithPostTitles(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function approveComment(string $id): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            (new BlogComment())->approve((int) $id);
        }

        header('Location: /admin/comments');
        exit;
    }

    public function deleteComment(string $id): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            (new BlogComment())->delete((int) $id);
        }

        header('Location: /admin/comments');
        exit;
    }

    private function save(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/blog/create' : "/admin/blog/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

        $errors = [];
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }
        if ($body === '') {
            $errors['body'] = 'Body is required.';
        }

        $featuredImagePath = null;
        try {
            $featuredImagePath = Upload::store($_FILES['featured_image'] ?? [], 'blog', ['jpg', 'jpeg', 'png', 'webp']);
        } catch (RuntimeException $e) {
            $errors['featured_image'] = $e->getMessage();
        }

        if (!empty($errors)) {
            $_SESSION['post_errors'] = $errors;
            header('Location: ' . $redirectTo);
            exit;
        }

        $model = new BlogPost();
        $existing = $id !== null ? $model->find($id) : null;

        $slug = $this->uniqueSlug(Slug::make($title), $id);

        $existingPublishedAt = $existing !== null ? $existing['published_at'] : null;
        $publishedAt = $status === 'published'
            ? ($existingPublishedAt ?? date('Y-m-d H:i:s'))
            : $existingPublishedAt;

        if ($id === null) {
            $model->create($title, $slug, $body, $featuredImagePath, $status, $publishedAt);
        } else {
            if ($featuredImagePath !== null && $existing !== null && !empty($existing['featured_image_path'])) {
                Upload::delete($existing['featured_image_path']);
            }
            $model->update($id, $title, $slug, $body, $featuredImagePath, $status, $publishedAt);
        }

        header('Location: /admin/blog');
        exit;
    }

    /**
     * Appends -2, -3, ... if the slug collides with another post's (excluding
     * the post currently being edited) - blog_posts.slug is UNIQUE.
     */
    private function uniqueSlug(string $baseSlug, ?int $excludeId): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (true) {
            $stmt = Database::connection()->prepare('SELECT id FROM blog_posts WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $excludeId ?? 0]);

            if ($stmt->fetch() === false) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
    }
}
