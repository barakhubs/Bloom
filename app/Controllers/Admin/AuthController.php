<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AdminUser;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: /admin');
            exit;
        }

        $errors = $_SESSION['login_errors'] ?? [];
        $old = $_SESSION['login_old'] ?? [];
        unset($_SESSION['login_errors'], $_SESSION['login_old']);

        $this->renderRaw('admin/auth/login', [
            'title' => 'Admin Login - Bloom Beyond Borders',
            'errors' => $errors,
            'old' => $old,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->fail(['general' => 'Your session expired. Please try again.'], $email);

            return;
        }

        $user = (new AdminUser())->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $this->fail(['general' => 'Incorrect email or password.'], $email);

            return;
        }

        Auth::login((int) $user['id']);
        header('Location: /admin');
        exit;
    }

    public function logout(): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            Auth::logout();
        }
        header('Location: /admin/login');
        exit;
    }

    private function fail(array $errors, string $email): void
    {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_old'] = ['email' => $email];
        header('Location: /admin/login');
        exit;
    }
}
