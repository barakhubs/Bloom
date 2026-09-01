<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Upload;
use App\Models\TeamMember;

class TeamController extends AdminController
{
    public function index(): void
    {
        $this->view('admin/team/index', [
            'title' => 'Team - Bloom Beyond Borders Admin',
            'members' => (new TeamMember())->all(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $errors = $_SESSION['team_errors'] ?? [];
        unset($_SESSION['team_errors']);

        $this->view('admin/team/form', [
            'title' => 'Add Team Member - Bloom Beyond Borders Admin',
            'member' => null,
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
        $member = (new TeamMember())->find((int) $id);

        if ($member === null) {
            (new ErrorController())->notFound();

            return;
        }

        $errors = $_SESSION['team_errors'] ?? [];
        unset($_SESSION['team_errors']);

        $this->view('admin/team/form', [
            'title' => 'Edit Team Member - Bloom Beyond Borders Admin',
            'member' => $member,
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
            header('Location: /admin/team');
            exit;
        }

        $model = new TeamMember();
        $member = $model->find((int) $id);

        if ($member !== null) {
            $model->delete((int) $id);
            if (!empty($member['photo_path'])) {
                Upload::delete($member['photo_path']);
            }
        }

        header('Location: /admin/team');
        exit;
    }

    private function save(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/team/create' : "/admin/team/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $title = trim((string) ($_POST['title'] ?? ''));
        $bio = trim((string) ($_POST['bio'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }

        $photoPath = null;
        try {
            $photoPath = Upload::store($_FILES['photo'] ?? [], 'team', ['jpg', 'jpeg', 'png', 'webp']);
        } catch (\RuntimeException $e) {
            $errors['photo'] = $e->getMessage();
        }

        if (!empty($errors)) {
            $_SESSION['team_errors'] = $errors;
            header('Location: ' . $redirectTo);
            exit;
        }

        $model = new TeamMember();

        if ($id === null) {
            $model->create($name, $title, $bio, $photoPath, $sortOrder);
        } else {
            $existing = $model->find($id);
            if ($photoPath !== null && $existing !== null && !empty($existing['photo_path'])) {
                Upload::delete($existing['photo_path']);
            }
            $model->update($id, $name, $title, $bio, $photoPath, $sortOrder);
        }

        header('Location: /admin/team');
        exit;
    }
}
