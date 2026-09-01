<?php
/** @var array|null $album */
/** @var array $errors */
/** @var string $csrfToken */
$isEdit = $album !== null;
$actionUrl = $isEdit ? '/admin/gallery/' . (int) $album['id'] : '/admin/gallery';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Album' : 'Add Album' ?></h2>

<form action="<?= $actionUrl ?>" method="post" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Name</label>
            <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($album['name'] ?? '') ?>">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= (int) ($album['sort_order'] ?? 0) ?>">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Album' ?></button>
        <a href="/admin/gallery" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
