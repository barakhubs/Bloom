<?php
/** @var array|null $user */
/** @var array $values */
/** @var array $roles */
/** @var bool $isSelf */
/** @var array $errors */
/** @var string $csrfToken */
$isEdit = $user !== null;
$actionUrl = $isEdit ? '/admin/users/' . (int) $user['id'] : '/admin/users';
$selectedRole = (int) ($values['role_id'] ?? 0);
$selectedStatus = ($values['status'] ?? 'active') === 'disabled' ? 'disabled' : 'active';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit User' : 'Invite User' ?></h2>

<form action="<?= $actionUrl ?>" method="post" class="bg-white p-4 shadow-sm" style="max-width: 720px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <?php if (!$isEdit): ?>
        <p class="text-muted">They'll get an email with a link to choose their own password. The link is valid for 72 hours.</p>
    <?php elseif ($user['status'] === 'invited'): ?>
        <div class="alert alert-warning">This person hasn't accepted their invite yet. You can resend it from the Users list.</div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($values['name'] ?? '') ?>">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" name="email" value="<?= htmlspecialchars($values['email'] ?? '') ?>">
            <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <?php if ($isSelf): ?>
                <input type="hidden" name="role_id" value="<?= $selectedRole ?>">
            <?php endif; ?>
            <select class="form-select<?= isset($errors['role_id']) ? ' is-invalid' : '' ?>" name="role_id"<?= $isSelf ? ' disabled' : '' ?>>
                <option value="">Choose a role&hellip;</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= (int) $role['id'] ?>"<?= (int) $role['id'] === $selectedRole ? ' selected' : '' ?>><?= htmlspecialchars($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['role_id'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['role_id']) ?></div><?php endif; ?>
            <?php if ($isSelf): ?><div class="form-text">You can't change your own role.</div><?php endif; ?>
        </div>
        <?php if ($isEdit): ?>
            <div class="col-md-6">
                <label class="form-label">Access</label>
                <?php if ($isSelf): ?>
                    <input type="hidden" name="status" value="active">
                <?php endif; ?>
                <select class="form-select<?= isset($errors['status']) ? ' is-invalid' : '' ?>" name="status"<?= $isSelf ? ' disabled' : '' ?>>
                    <option value="active"<?= $selectedStatus === 'active' ? ' selected' : '' ?>>Enabled</option>
                    <option value="disabled"<?= $selectedStatus === 'disabled' ? ' selected' : '' ?>>Disabled (can't log in)</option>
                </select>
                <?php if (isset($errors['status'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['status']) ?></div><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Send Invite' ?></button>
        <a href="/admin/users" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
