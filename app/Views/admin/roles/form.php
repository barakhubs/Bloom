<?php
/** @var array|null $role */
/** @var string $name */
/** @var string[] $granted */
/** @var array $errors */
/** @var string $csrfToken */

use App\Core\Permissions;

$isEdit = $role !== null;
$actionUrl = $isEdit ? '/admin/roles/' . (int) $role['id'] : '/admin/roles';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Role' : 'Add Role' ?></h2>

<form action="<?= $actionUrl ?>" method="post" class="bg-white p-4 shadow-sm" style="max-width: 720px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="mb-4">
        <label class="form-label">Role name</label>
        <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Blog Editor">
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
    </div>

    <label class="form-label d-block">Sections this role can manage</label>
    <div class="row g-2">
        <?php foreach (Permissions::ALL as $slug => $label): ?>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= htmlspecialchars($slug) ?>" id="perm-<?= htmlspecialchars($slug) ?>"<?= in_array($slug, $granted, true) ? ' checked' : '' ?>>
                    <label class="form-check-label" for="perm-<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($label) ?></label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="form-text mt-2">Everyone can see the Dashboard and their own account. "Users &amp; Roles" lets someone invite users and change roles, so grant it carefully.</div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Role' ?></button>
        <a href="/admin/roles" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
