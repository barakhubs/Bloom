<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AccountLinks;
use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Models\AdminUser;
use App\Models\Role;

class UsersController extends AdminController
{
    protected ?string $permission = 'users';

    public function index(): void
    {
        $flash = $_SESSION['users_flash'] ?? null;
        unset($_SESSION['users_flash']);

        $this->view('admin/users/index', [
            'title' => 'Users - Bloom Beyond Borders Admin',
            'users' => (new AdminUser())->all(),
            'flash' => $flash,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $this->form(null, 'Invite User');
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $user = (new AdminUser())->findById((int) $id);

        if ($user === null) {
            (new ErrorController())->notFound();

            return;
        }

        if (!$this->canManage($user)) {
            $this->flash('danger', 'Only a Super Admin can edit a Super Admin.');
            header('Location: /admin/users');
            exit;
        }

        $this->form($user, 'Edit User');
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/users');
            exit;
        }

        $model = new AdminUser();
        $user = $model->findById((int) $id);

        if ($user !== null) {
            if ((int) $user['id'] === Auth::id()) {
                $this->flash('danger', "You can't delete your own account.");
            } elseif (!$this->canManage($user)) {
                $this->flash('danger', 'Only a Super Admin can delete a Super Admin.');
            } elseif ($this->isLastActiveSuperAdmin($user)) {
                $this->flash('danger', "You can't delete the last active Super Admin.");
            } else {
                $model->delete((int) $user['id']);
                $this->flash('success', 'Deleted ' . $user['email'] . '.');
            }
        }

        header('Location: /admin/users');
        exit;
    }

    /** Re-sends the invite (invited users) or sends a password-reset link (active users). */
    public function sendLink(string $id): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/users');
            exit;
        }

        $user = (new AdminUser())->findById((int) $id);

        if ($user === null || !$this->canManage($user)) {
            header('Location: /admin/users');
            exit;
        }

        if ($user['status'] === 'disabled') {
            $this->flash('danger', 'Disabled accounts must be re-enabled before they can be sent a link.');
            header('Location: /admin/users');
            exit;
        }

        $this->sendAccountLink($user, $user['status'] === 'invited' ? 'invite' : 'reset');
        header('Location: /admin/users');
        exit;
    }

    private function form(?array $user, string $heading): void
    {
        $errors = $_SESSION['user_errors'] ?? [];
        $old = $_SESSION['user_old'] ?? null;
        unset($_SESSION['user_errors'], $_SESSION['user_old']);

        $this->view('admin/users/form', [
            'title' => $heading . ' - Bloom Beyond Borders Admin',
            'user' => $user,
            'values' => $old ?? $user ?? [],
            'roles' => array_filter(
                (new Role())->all(),
                fn (array $role) => empty($role['is_system']) || Auth::isSuperAdmin()
            ),
            'isSelf' => $user !== null && (int) $user['id'] === Auth::id(),
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    private function save(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/users/create' : "/admin/users/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $model = new AdminUser();
        $existing = $id !== null ? $model->findById($id) : null;

        if ($id !== null && ($existing === null || !$this->canManage($existing))) {
            header('Location: /admin/users');
            exit;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($model->emailTaken($email, $id)) {
            $errors['email'] = 'Another user already has this email.';
        }

        $role = (new Role())->find($roleId);
        if ($role === null) {
            $errors['role_id'] = 'Choose a role.';
        } elseif (!empty($role['is_system']) && !Auth::isSuperAdmin()) {
            $errors['role_id'] = 'Only a Super Admin can grant the Super Admin role.';
        }

        if ($existing !== null) {
            // An invited user stays 'invited' until they set a password; the
            // form only toggles between enabled and disabled.
            if (!in_array($status, ['active', 'disabled'], true)) {
                $status = $existing['status'] === 'disabled' ? 'disabled' : 'active';
            }
            if ($status === 'active' && $existing['status'] === 'invited') {
                $status = 'invited';
            }

            $isSelf = (int) $existing['id'] === Auth::id();
            $losesSuperAdmin = ($role !== null && empty($role['is_system'])) || $status !== 'active';

            if ($isSelf && $roleId !== (int) $existing['role_id']) {
                $errors['role_id'] = "You can't change your own role.";
            }
            if ($isSelf && $status === 'disabled') {
                $errors['status'] = "You can't disable your own account.";
            }
            if ($losesSuperAdmin && $this->isLastActiveSuperAdmin($existing)) {
                $errors['role_id'] = 'This is the last active Super Admin - promote someone else first.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['user_errors'] = $errors;
            $_SESSION['user_old'] = ['name' => $name, 'email' => $email, 'role_id' => $roleId, 'status' => $status];
            header('Location: ' . $redirectTo);
            exit;
        }

        if ($existing === null) {
            $newId = $model->create($name, $email, $roleId);
            $this->sendAccountLink($model->findById($newId), 'invite');
        } else {
            $model->update($id, $name, $email, $roleId, $status);
            $this->flash('success', 'Saved ' . $email . '.');
        }

        header('Location: /admin/users');
        exit;
    }

    private function sendAccountLink(array $user, string $purpose): void
    {
        $result = AccountLinks::send($user, $purpose);
        $what = $purpose === 'invite' ? 'Invite' : 'Password reset link';

        if ($result['sent']) {
            $this->flash('success', "{$what} emailed to {$user['email']}.");

            return;
        }

        $this->flash(
            'warning',
            "{$what} for {$user['email']} couldn't be emailed (check SMTP in Settings). Copy this link and send it to them yourself:",
            $result['url']
        );
    }

    /**
     * Users-permission holders who aren't Super Admins can't touch Super Admin
     * accounts - otherwise "manage users" would be a path to full control.
     */
    private function canManage(array $user): bool
    {
        return empty($user['is_system']) || Auth::isSuperAdmin();
    }

    private function isLastActiveSuperAdmin(array $user): bool
    {
        return !empty($user['is_system'])
            && $user['status'] === 'active'
            && (new AdminUser())->countActiveSuperAdmins() <= 1;
    }

    private function flash(string $type, string $message, ?string $link = null): void
    {
        $_SESSION['users_flash'] = ['type' => $type, 'message' => $message, 'link' => $link];
    }
}
