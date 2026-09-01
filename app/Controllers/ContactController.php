<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Models\ContactSubmission;
use App\Models\Setting;

class ContactController extends Controller
{
    public function index(): void
    {
        $errors = $_SESSION['contact_errors'] ?? [];
        $old = $_SESSION['contact_old'] ?? [];
        $success = $_SESSION['contact_success'] ?? false;
        unset($_SESSION['contact_errors'], $_SESSION['contact_old'], $_SESSION['contact_success']);

        $this->view('contact/index', [
            'title' => 'Contact - Bloom Beyond Borders',
            'pageTitle' => 'Contact',
            'errors' => $errors,
            'old' => $old,
            'success' => $success,
            'csrfToken' => Csrf::token(),
            'settings' => Setting::all(),
        ]);
    }

    public function submit(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $honeypot = trim((string) ($_POST['website'] ?? ''));

        // Honeypot tripped: silently pretend success, do nothing further.
        if ($honeypot !== '') {
            $this->redirectWithSuccess();

            return;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->redirectWithErrors(['general' => 'Your session expired. Please try again.'], $name, $email, $message);

            return;
        }

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }
        if ($message === '') {
            $errors['message'] = 'Message is required.';
        }

        if (!empty($errors)) {
            $this->redirectWithErrors($errors, $name, $email, $message);

            return;
        }

        (new ContactSubmission())->create($name, $email, $message);
        Mailer::sendContactNotification($name, $email, $message);

        $this->redirectWithSuccess();
    }

    private function redirectWithErrors(array $errors, string $name, string $email, string $message): void
    {
        $_SESSION['contact_errors'] = $errors;
        $_SESSION['contact_old'] = ['name' => $name, 'email' => $email, 'message' => $message];
        header('Location: /contact');
        exit;
    }

    private function redirectWithSuccess(): void
    {
        $_SESSION['contact_success'] = true;
        header('Location: /contact');
        exit;
    }
}
