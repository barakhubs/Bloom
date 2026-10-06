<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Permissions;
use App\Models\Role;

class RolesController extends AdminController
{
    protected ?string $permission = 'users';

    public function index(): void
    {
        $flash = $_SESSION['roles_flash'] ?? null;
        unset($_SESSION['roles_flash']);

        $model = new Role();
        $roles = $model->all();
        foreach ($roles as &$role) {
            $role['permissions'] = $model->permissions((int) $role['id']);
        }
        unset($role);

        $this->view('admin/roles/index', [
            'title' => 'Roles - Bloom Beyond Borders Admin',
            'roles' => $roles,
            'flash' => $flash,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $this->form(null, []);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $model = new Role();
        $role = $model->find((int) $id);

        if ($role === null || !empty($role['is_system'])) {
            (new ErrorController())->notFound();

            return;
        }

        $this->form($role, $model->permissions((int) $role['id']));
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/roles');
            exit;
        }

        $model = new Role();
        $role = $model->find((int) $id);

        if ($role !== null && empty($role['is_system'])) {
            $userCount = $model->userCount((int) $role['id']);

            if ($userCount > 0) {
                $_SESSION['roles_flash'] = [
                    'type' => 'danger',
                    'message' => "\"{$role['name']}\" is still assigned to {$userCount} user(s) - move them to another role first.",
                ];
            } else {
                $model->delete((int) $role['id']);
            }
        }

        header('Location: /admin/roles');
        exit;
    }

    private function form(?array $role, array $permissions): void
    {
        $errors = $_SESSION['role_errors'] ?? [];
        $old = $_SESSION['role_old'] ?? null;
        unset($_SESSION['role_errors'], $_SESSION['role_old']);

        $this->view('admin/roles/form', [
            'title' => ($role === null ? 'Add Role' : 'Edit Role') . ' - Bloom Beyond Borders Admin',
            'role' => $role,
            'name' => $old['name'] ?? $role['name'] ?? '',
            'granted' => $old['permissions'] ?? $permissions,
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    private function save(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/roles/create' : "/admin/roles/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $model = new Role();

        if ($id !== null) {
            $existing = $model->find($id);
            if ($existing === null || !empty($existing['is_system'])) {
                header('Location: /admin/roles');
                exit;
            }
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $permissions = array_values(array_intersect(
            array_keys(Permissions::ALL),
            (array) ($_POST['permissions'] ?? [])
        ));

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        } elseif ($model->nameTaken($name, $id)) {
            $errors['name'] = 'Another role already has this name.';
        }

        if (!empty($errors)) {
            $_SESSION['role_errors'] = $errors;
            $_SESSION['role_old'] = ['name' => $name, 'permissions' => $permissions];
            header('Location: ' . $redirectTo);
            exit;
        }

        if ($id === null) {
            $model->create($name, $permissions);
        } else {
            $model->update($id, $name, $permissions);
        }

        header('Location: /admin/roles');
        exit;
    }
}
