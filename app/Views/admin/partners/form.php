<?php
/** @var array|null $partner */
/** @var array $errors */
/** @var string $csrfToken */
$isEdit = $partner !== null;
$actionUrl = $isEdit ? '/admin/partners/' . (int) $partner['id'] : '/admin/partners';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Partner' : 'Add Partner' ?></h2>

<form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($partner['name'] ?? '') ?>">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Link URL (optional)</label>
            <input type="url" class="form-control" name="link_url" value="<?= htmlspecialchars($partner['link_url'] ?? '') ?>" placeholder="https://">
        </div>
        <div class="col-md-8">
            <label class="form-label d-block">Logo</label>
            <?php if (!empty($partner['logo_path'])): ?>
                <img src="/<?= htmlspecialchars(ltrim($partner['logo_path'], '/')) ?>" alt="" style="max-height:50px;" class="mb-2 d-block">
            <?php endif; ?>
            <input type="file" class="form-control<?= isset($errors['logo']) ? ' is-invalid' : '' ?>" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg">
            <?php if (isset($errors['logo'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['logo']) ?></div><?php endif; ?>
            <?php if (!$isEdit): ?><div class="form-text">Required.</div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= (int) ($partner['sort_order'] ?? 0) ?>">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Partner' ?></button>
        <a href="/admin/partners" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
