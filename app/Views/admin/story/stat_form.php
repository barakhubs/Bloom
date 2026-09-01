<?php
/** @var array|null $stat */
/** @var array $errors */
/** @var string $csrfToken */
$isEdit = $stat !== null;
$actionUrl = $isEdit ? '/admin/story/stats/' . (int) $stat['id'] : '/admin/story/stats';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Stat' : 'Add Stat' ?></h2>

<form action="<?= $actionUrl ?>" method="post" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Label</label>
            <input type="text" class="form-control<?= isset($errors['label']) ? ' is-invalid' : '' ?>" name="label" value="<?= htmlspecialchars($stat['label'] ?? '') ?>">
            <?php if (isset($errors['label'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['label']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Value</label>
            <input type="text" class="form-control<?= isset($errors['value']) ? ' is-invalid' : '' ?>" name="value" value="<?= htmlspecialchars($stat['value'] ?? '') ?>">
            <?php if (isset($errors['value'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['value']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-2">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= (int) ($stat['sort_order'] ?? 0) ?>">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Stat' ?></button>
        <a href="/admin/story" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
