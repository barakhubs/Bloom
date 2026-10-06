<?php

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Models\AdminUser;

/** "My account" - every logged-in user can change their own name and password. */
class AccountController extends AdminController
{
    public function index(): void
    {
        $errors = $_SESSION['account_errors'] ?? [];
        $saved = $_SESSION['account_saved'] ?? false;
        unset($_SESSION['account_errors'], $_SESSION['account_saved']);

        $this->view('admin/account/index', [
            'title' => 'My Account - Bloom Beyond Borders Admin',
            'user' => Auth::user(),
            'errors' => $errors,
            'saved' => $saved,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/account');
            exit;
        }

        $user = Auth::user();
        $name = trim((string) ($_POST['name'] ?? ''));
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        $changingPassword = $newPassword !== '' || $confirmPassword !== '';

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($changingPassword) {
            if (!password_verify($currentPassword, (string) $user['password_hash'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            }
            $passwordError = self::passwordError($newPassword, $confirmPassword);
            if ($passwordError !== null) {
                $errors['new_password'] = $passwordError;
            }
        }

        if (!empty($errors)) {
            $_SESSION['account_errors'] = $errors;
            header('Location: /admin/account');
            exit;
        }

        $model = new AdminUser();
        $model->updateName((int) $user['id'], $name);
        if ($changingPassword) {
            $model->setPassword((int) $user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
        }

        $_SESSION['account_saved'] = true;
        header('Location: /admin/account');
        exit;
    }

    /** Shared with the invite/reset set-password screen. */
    public static function passwordError(string $password, string $confirmation): ?string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if ($password !== $confirmation) {
            return "Passwords don't match.";
        }

        return null;
    }
}
