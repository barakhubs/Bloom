<?php
/** @var array|null $entry */
/** @var array $errors */
/** @var string $csrfToken */
$isEdit = $entry !== null;
$actionUrl = $isEdit ? '/admin/story/finance/' . (int) $entry['id'] : '/admin/story/finance';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Financial Entry' : 'Add Financial Entry' ?></h2>

<form action="<?= $actionUrl ?>" method="post" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Year</label>
            <input type="number" class="form-control<?= isset($errors['year']) ? ' is-invalid' : '' ?>" name="year" value="<?= htmlspecialchars((string) ($entry['year'] ?? date('Y'))) ?>">
            <?php if (isset($errors['year'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['year']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-5">
            <label class="form-label">Category</label>
            <input type="text" class="form-control<?= isset($errors['category']) ? ' is-invalid' : '' ?>" name="category" value="<?= htmlspecialchars($entry['category'] ?? '') ?>">
            <?php if (isset($errors['category'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['category']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-2">
            <label class="form-label">Percentage</label>
            <input type="number" step="0.01" class="form-control<?= isset($errors['percentage']) ? ' is-invalid' : '' ?>" name="percentage" value="<?= htmlspecialchars((string) ($entry['percentage'] ?? '')) ?>">
            <?php if (isset($errors['percentage'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['percentage']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-2">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= (int) ($entry['sort_order'] ?? 0) ?>">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Entry' ?></button>
        <a href="/admin/story" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
