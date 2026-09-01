<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\BlogComment;
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

        $errors = $_SESSION['comment_errors'] ?? [];
        $old = $_SESSION['comment_old'] ?? [];
        $success = $_SESSION['comment_success'] ?? false;
        unset($_SESSION['comment_errors'], $_SESSION['comment_old'], $_SESSION['comment_success']);

        $this->view('blog/show', [
            'title' => $post['title'] . ' - Bloom Beyond Borders',
            'pageTitle' => $post['title'],
            'post' => $post,
            'comments' => (new BlogComment())->approvedForPost((int) $post['id']),
            'errors' => $errors,
            'old' => $old,
            'success' => $success,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function submitComment(string $slug): void
    {
        $post = (new BlogPost())->findPublishedBySlug($slug);

        if ($post === null) {
            (new ErrorController())->notFound();

            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $website = trim((string) ($_POST['website'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $honeypot = trim((string) ($_POST['phone'] ?? ''));

        // Honeypot tripped: silently pretend success, do nothing further.
        if ($honeypot !== '') {
            $this->redirectWithSuccess($slug);

            return;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->redirectWithErrors($slug, ['general' => 'Your session expired. Please try again.'], $name, $email, $website, $body);

            return;
        }

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }
        if ($body === '') {
            $errors['body'] = 'Comment is required.';
        }

        if (!empty($errors)) {
            $this->redirectWithErrors($slug, $errors, $name, $email, $website, $body);

            return;
        }

        (new BlogComment())->create((int) $post['id'], $name, $email, $website !== '' ? $website : null, $body);

        $this->redirectWithSuccess($slug);
    }

    private function redirectWithErrors(string $slug, array $errors, string $name, string $email, string $website, string $body): void
    {
        $_SESSION['comment_errors'] = $errors;
        $_SESSION['comment_old'] = ['name' => $name, 'email' => $email, 'website' => $website, 'body' => $body];
        header('Location: /blog/' . $slug . '#comments');
        exit;
    }

    private function redirectWithSuccess(string $slug): void
    {
        $_SESSION['comment_success'] = true;
        header('Location: /blog/' . $slug . '#comments');
        exit;
    }
}
