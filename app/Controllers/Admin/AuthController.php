<?php

namespace App\Controllers\Admin;

use App\Core\AccountLinks;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AdminUser;
use App\Models\AdminUserToken;

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

        $model = new AdminUser();
        $user = $model->findByEmail($email);

        // Invited (no password yet) and disabled accounts get the same generic
        // message as a wrong password, so the form doesn't reveal account state.
        if (
            $user === null
            || $user['status'] !== 'active'
            || $user['password_hash'] === null
            || !password_verify($password, $user['password_hash'])
        ) {
            $this->fail(['general' => 'Incorrect email or password.'], $email);

            return;
        }

        Auth::login((int) $user['id']);
        $model->touchLogin((int) $user['id']);
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

    public function showSetPassword(string $token): void
    {
        $tokenRow = (new AdminUserToken())->findValid($token);
        $user = $tokenRow !== null ? (new AdminUser())->findById((int) $tokenRow['user_id']) : null;

        if ($user !== null && $user['status'] === 'disabled') {
            $user = null;
        }

        $errors = $_SESSION['set_password_errors'] ?? [];
        unset($_SESSION['set_password_errors']);

        $this->renderRaw('admin/auth/set_password', [
            'title' => 'Set Password - Bloom Beyond Borders',
            'token' => $token,
            'user' => $user,
            'isInvite' => $tokenRow !== null && $tokenRow['purpose'] === 'invite',
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function setPassword(string $token): void
    {
        $redirectTo = '/admin/set-password/' . rawurlencode($token);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $_SESSION['set_password_errors'] = ['general' => 'Your session expired. Please try again.'];
            header('Location: ' . $redirectTo);
            exit;
        }

        $tokens = new AdminUserToken();
        $tokenRow = $tokens->findValid($token);
        $model = new AdminUser();
        $user = $tokenRow !== null ? $model->findById((int) $tokenRow['user_id']) : null;

        if ($user === null || $user['status'] === 'disabled') {
            header('Location: ' . $redirectTo);
            exit;
        }

        $password = (string) ($_POST['password'] ?? '');
        $passwordError = AccountController::passwordError($password, (string) ($_POST['password_confirmation'] ?? ''));

        if ($passwordError !== null) {
            $_SESSION['set_password_errors'] = ['password' => $passwordError];
            header('Location: ' . $redirectTo);
            exit;
        }

        if (!$tokens->consume((int) $tokenRow['id'])) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $model->setPassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        Auth::login((int) $user['id']);
        $model->touchLogin((int) $user['id']);
        header('Location: /admin');
        exit;
    }

    public function showForgotPassword(): void
    {
        $sent = $_SESSION['forgot_sent'] ?? false;
        unset($_SESSION['forgot_sent']);

        $this->renderRaw('admin/auth/forgot_password', [
            'title' => 'Forgot Password - Bloom Beyond Borders',
            'sent' => $sent,
            'csrfToken' => Csrf::token(),
        ]);
    }

    /**
     * Always shows the same confirmation whether or not the email matched an
     * account, so the form can't be used to discover who has admin access.
     */
    public function forgotPassword(): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            $email = trim((string) ($_POST['email'] ?? ''));
            $user = $email !== '' ? (new AdminUser())->findByEmail($email) : null;

            if ($user !== null && $user['status'] === 'active') {
                AccountLinks::send($user, 'reset');
            }

            $_SESSION['forgot_sent'] = true;
        }

        header('Location: /admin/forgot-password');
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
