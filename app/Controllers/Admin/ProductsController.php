<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Money;
use App\Core\Shop;
use App\Core\Slug;
use App\Core\Upload;
use App\Models\Product;

class ProductsController extends AdminController
{
    protected ?string $permission = 'shop';

    public function index(): void
    {
        $this->view('admin/products/index', [
            'title' => 'Products - Bloom Beyond Borders Admin',
            'products' => (new Product())->all(),
            'shopEnabled' => Shop::enabled(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        $errors = $_SESSION['product_errors'] ?? [];
        $old = $_SESSION['product_old'] ?? null;
        unset($_SESSION['product_errors'], $_SESSION['product_old']);

        $this->view('admin/products/form', [
            'title' => 'Add Product - Bloom Beyond Borders Admin',
            'product' => $old,
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
        $product = (new Product())->find((int) $id);

        if ($product === null) {
            (new ErrorController())->notFound();

            return;
        }

        $errors = $_SESSION['product_errors'] ?? [];
        $old = $_SESSION['product_old'] ?? null;
        unset($_SESSION['product_errors'], $_SESSION['product_old']);

        $this->view('admin/products/form', [
            'title' => 'Edit Product - Bloom Beyond Borders Admin',
            'product' => $old !== null ? $old + $product : $product,
            'isEdit' => true,
            'errors' => $errors,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    /** Past orders keep their snapshot line items (product_id becomes NULL). */
    public function destroy(string $id): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/products');
            exit;
        }

        $model = new Product();
        $product = $model->find((int) $id);

        if ($product !== null) {
            $model->delete((int) $id);
            if (!empty($product['image_path'])) {
                Upload::delete($product['image_path']);
            }
        }

        header('Location: /admin/products');
        exit;
    }

    private function save(?int $id): void
    {
        $redirectTo = $id === null ? '/admin/products/create' : "/admin/products/{$id}/edit";

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $redirectTo);
            exit;
        }

        $model = new Product();
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
        $priceInput = trim((string) ($_POST['price'] ?? ''));
        $priceCents = Money::parse($priceInput);
        $stockInput = trim((string) ($_POST['stock'] ?? '0'));
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($model->slugExists($slug, $id)) {
            $errors['slug'] = 'Another product already uses this URL slug.';
        }
        if ($priceCents === null || $priceCents < 50) {
            $errors['price'] = 'Enter a price in dollars of at least $0.50 (Stripe\'s minimum), e.g. 25 or 24.99.';
        }
        if (!preg_match('/^\d{1,6}$/', $stockInput)) {
            $errors['stock'] = 'Stock must be a whole number (0 or more).';
        }

        $imagePath = null;
        if (empty($errors)) {
            try {
                $imagePath = Upload::store($_FILES['image'] ?? [], 'products', ['jpg', 'jpeg', 'png', 'webp']);
            } catch (\RuntimeException $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $_SESSION['product_errors'] = $errors;
            $_SESSION['product_old'] = [
                'name' => $name,
                'slug' => $slugInput,
                'summary' => $summary,
                'description' => $description,
                'price_input' => $priceInput,
                'stock' => $stockInput,
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
            'price_cents' => $priceCents,
            'stock' => (int) $stockInput,
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

        header('Location: /admin/products');
        exit;
    }
}
