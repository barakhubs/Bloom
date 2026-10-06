<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Upload;
use App\Models\Partner;

class PartnersController extends AdminController
{
    protected ?string $permission = 'partners';

    public function index(): void
    {
        $this->view('admin/partners/index', [
            'title' => 'Partners - Bloom Beyond Borders Admin',
            'partners' => (new Partner())->all(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $errors = $_SESSION['partner_errors'] ?? [];
        unset($_SESSION['partner_errors']);

        $this->view('admin/partners/form', [
            'title' => 'Add Partner - Bloom Beyond Borders Admin',
            'partner' => null,
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
        $partner = (new Partner())->find((int) $id);

        if ($partner === null) {
            (new ErrorController())->notFound();

            return;
        }

        $errors = $_SESSION['partner_errors'] ?? [];
        unset($_SESSION['partner_errors']);

        $this->view('admin/partners/form', [
            'title' => 'Edit Partner - Bloom Beyond Borders Admin',
            'partner' => $partner,
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
            header('Location: /admin/partners');
            exit;
        }

        $model = new Partner();
        $partner = $model->find((int) $id);

        if ($partner !== null) {
            $model->delete((int) $id);
            if (!empty($partner['logo_path'])) {
                Upload::delete($partner['logo_path']);
            }
        }

        header('Location: /admin/partners');
        exit;
    }

    private function save(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/partners/create' : "/admin/partners/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $tagline = trim((string) ($_POST['tagline'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $linkUrl = trim((string) ($_POST['link_url'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }

        $logoPath = null;
        try {
            $logoPath = Upload::store($_FILES['logo'] ?? [], 'partners', ['jpg', 'jpeg', 'png', 'webp', 'svg']);
        } catch (\RuntimeException $e) {
            $errors['logo'] = $e->getMessage();
        }

        if (!empty($errors)) {
            $_SESSION['partner_errors'] = $errors;
            header('Location: ' . $redirectTo);
            exit;
        }

        $model = new Partner();
        $linkUrlOrNull = $linkUrl !== '' ? $linkUrl : null;
        $taglineOrNull = $tagline !== '' ? $tagline : null;
        $descriptionOrNull = $description !== '' ? $description : null;

        if ($id === null) {
            $model->create($name, $logoPath, $taglineOrNull, $descriptionOrNull, $linkUrlOrNull, $sortOrder);
        } else {
            $existing = $model->find($id);
            if ($logoPath !== null && $existing !== null && !empty($existing['logo_path'])) {
                Upload::delete($existing['logo_path']);
            }
            $model->update($id, $name, $logoPath, $taglineOrNull, $descriptionOrNull, $linkUrlOrNull, $sortOrder);
        }

        header('Location: /admin/partners');
        exit;
    }
}
