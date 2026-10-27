<?php
/** @var array|null $product */
/** @var bool $isEdit */
/** @var array $errors */
/** @var string $csrfToken */

use App\Core\Money;

$actionUrl = $isEdit ? '/admin/products/' . (int) $product['id'] : '/admin/products';
$priceValue = $product['price_input'] ?? (isset($product['price_cents']) ? Money::toInput((int) $product['price_cents']) : '');
$isActive = $product === null || (int) ($product['is_active'] ?? 1) === 1;
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h2>

<form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">URL Slug (optional)</label>
            <div class="input-group has-validation">
                <span class="input-group-text">/shop/</span>
                <input type="text" class="form-control<?= isset($errors['slug']) ? ' is-invalid' : '' ?>" name="slug" value="<?= htmlspecialchars($product['slug'] ?? '') ?>" placeholder="generated from name">
                <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['slug']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Price (USD)</label>
            <input type="text" inputmode="decimal" class="form-control<?= isset($errors['price']) ? ' is-invalid' : '' ?>" name="price" value="<?= htmlspecialchars($priceValue) ?>" placeholder="e.g. 24.99">
            <?php if (isset($errors['price'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Stock</label>
            <input type="number" min="0" class="form-control<?= isset($errors['stock']) ? ' is-invalid' : '' ?>" name="stock" value="<?= htmlspecialchars((string) ($product['stock'] ?? '0')) ?>">
            <?php if (isset($errors['stock'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['stock']) ?></div><?php endif; ?>
            <div class="form-text">Goes down automatically when an order is paid. At 0 it shows as sold out.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= (int) ($product['sort_order'] ?? 0) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Summary (optional)</label>
            <input type="text" class="form-control" name="summary" maxlength="255" value="<?= htmlspecialchars($product['summary'] ?? '') ?>" placeholder="One line shown on the product card">
        </div>
        <div class="col-12">
            <label class="form-label">Description (optional)</label>
            <textarea class="form-control" name="description" rows="8"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            <div class="form-text">Separate paragraphs with a blank line.</div>
        </div>
        <div class="col-md-8">
            <label class="form-label d-block">Image</label>
            <?php if (!empty($product['image_path'])): ?>
                <img src="/<?= htmlspecialchars(ltrim($product['image_path'], '/')) ?>" alt="" style="max-height:80px;" class="mb-2 d-block">
            <?php endif; ?>
            <input type="file" class="form-control<?= isset($errors['image']) ? ' is-invalid' : '' ?>" name="image" accept=".jpg,.jpeg,.png,.webp">
            <?php if (isset($errors['image'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"<?= $isActive ? ' checked' : '' ?>>
                <label class="form-check-label" for="is_active">Active (shown in the shop)</label>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Product' ?></button>
        <a href="/admin/products" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
