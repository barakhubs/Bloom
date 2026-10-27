<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Money;
use App\Core\Slug;
use App\Core\Upload;
use App\Models\DonationScheme;

class DonationSchemesController extends AdminController
{
    protected ?string $permission = 'donations';

    public function index(): void
    {
        $this->view('admin/donation-schemes/index', [
            'title' => 'Donation Schemes - Bloom Beyond Borders Admin',
            'schemes' => (new DonationScheme())->all(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $errors = $_SESSION['scheme_errors'] ?? [];
        $old = $_SESSION['scheme_old'] ?? null;
        unset($_SESSION['scheme_errors'], $_SESSION['scheme_old']);

        $this->view('admin/donation-schemes/form', [
            'title' => 'Add Donation Scheme - Bloom Beyond Borders Admin',
            'scheme' => $old,
            'isEdit' => false,
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
        $scheme = (new DonationScheme())->find((int) $id);

        if ($scheme === null) {
            (new ErrorController())->notFound();

            return;
        }

        $errors = $_SESSION['scheme_errors'] ?? [];
        $old = $_SESSION['scheme_old'] ?? null;
        unset($_SESSION['scheme_errors'], $_SESSION['scheme_old']);

        $this->view('admin/donation-schemes/form', [
            'title' => 'Edit Donation Scheme - Bloom Beyond Borders Admin',
            'scheme' => $old !== null ? $old + $scheme : $scheme,
            'isEdit' => true,
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    /** Past donations stay in the records; their scheme_id is set to NULL (FK ON DELETE SET NULL). */
    public function destroy(string $id): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/donation-schemes');
            exit;
        }

        $model = new DonationScheme();
        $scheme = $model->find((int) $id);

        if ($scheme !== null) {
            $model->delete((int) $id);
            if (!empty($scheme['image_path'])) {
                Upload::delete($scheme['image_path']);
            }
        }

        header('Location: /admin/donation-schemes');
        exit;
    }

    private function save(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/donation-schemes/create' : "/admin/donation-schemes/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $model = new DonationScheme();
        $existing = $id !== null ? $model->find($id) : null;

        if ($id !== null && $existing === null) {
            (new ErrorController())->notFound();

            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $slug = Slug::make($slugInput !== '' ? $slugInput : $name);
        $summary = trim((string) ($_POST['summary'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $presetsInput = trim((string) ($_POST['preset_amounts'] ?? ''));
        $presets = Money::parseList($presetsInput);
        $goalInput = trim((string) ($_POST['goal'] ?? ''));
        $goalCents = $goalInput !== '' ? Money::parse($goalInput) : null;
        $allowCustom = isset($_POST['allow_custom_amount']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($model->slugExists($slug, $id)) {
            $errors['slug'] = 'Another scheme already uses this URL slug.';
        }
        if ($presetsInput !== '' && empty($presets)) {
            $errors['preset_amounts'] = 'Enter amounts in dollars separated by commas, e.g. 25, 50, 100.';
        }
        if (empty($presets) && !$allowCustom) {
            $errors['preset_amounts'] = 'Add at least one suggested amount, or allow donors to enter their own.';
        }
        if (array_filter($presets, fn (int $cents) => $cents < 100)) {
            $errors['preset_amounts'] = 'Each suggested amount must be at least $1.';
        }
        if ($goalInput !== '' && ($goalCents === null || $goalCents <= 0)) {
            $errors['goal'] = 'Enter a goal in dollars, or leave it blank for no goal.';
        }

        $imagePath = null;
        if (empty($errors)) {
            try {
                $imagePath = Upload::store($_FILES['image'] ?? [], 'donations', ['jpg', 'jpeg', 'png', 'webp']);
            } catch (\RuntimeException $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $_SESSION['scheme_errors'] = $errors;
            $_SESSION['scheme_old'] = [
                'name' => $name,
                'slug' => $slugInput,
                'summary' => $summary,
                'description' => $description,
                'preset_amounts_input' => $presetsInput,
                'goal_input' => $goalInput,
                'allow_custom_amount' => $allowCustom,
                'is_active' => $isActive,
                'sort_order' => $sortOrder,
            ];
            header('Location: ' . $redirectTo);
            exit;
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'summary' => $summary !== '' ? $summary : null,
            'description' => $description !== '' ? $description : null,
            'preset_amounts' => implode(',', $presets),
            'allow_custom_amount' => $allowCustom,
            'goal_cents' => $goalCents,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ];

        if ($id === null) {
            $model->create($data, $imagePath);
        } else {
            if ($imagePath !== null && !empty($existing['image_path'])) {
                Upload::delete($existing['image_path']);
            }
            $model->update($id, $data, $imagePath);
        }

        header('Location: /admin/donation-schemes');
        exit;
    }
}
