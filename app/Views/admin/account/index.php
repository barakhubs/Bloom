<?php
/** @var array $user */
/** @var array $errors */
/** @var bool $saved */
/** @var string $csrfToken */
?>
<h2 class="mb-4">My Account</h2>

<?php if ($saved): ?>
    <div class="alert alert-success" role="alert">Your account has been updated.</div>
<?php endif; ?>

<form action="/admin/account" method="post" class="bg-white p-4 shadow-sm" style="max-width: 720px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($user['name']) ?>">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            <div class="form-text">Role: <?= htmlspecialchars($user['role_name']) ?>. Ask an administrator to change your email or role.</div>
        </div>
    </div>

    <h5 class="mt-4 mb-1">Change password</h5>
    <p class="text-muted small">Leave blank to keep your current password.</p>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Current password</label>
            <input type="password" class="form-control<?= isset($errors['current_password']) ? ' is-invalid' : '' ?>" name="current_password" autocomplete="current-password">
            <?php if (isset($errors['current_password'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['current_password']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">New password</label>
            <input type="password" class="form-control<?= isset($errors['new_password']) ? ' is-invalid' : '' ?>" name="new_password" autocomplete="new-password">
            <?php if (isset($errors['new_password'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['new_password']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Confirm new password</label>
            <input type="password" class="form-control" name="confirm_password" autocomplete="new-password">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
    </div>
</form>
