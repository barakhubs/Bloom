<?php
/** @var string $title */
/** @var string $token */
/** @var array|null $user */
/** @var bool $isInvite */
/** @var array $errors */
/** @var string $csrfToken */

$heading = $isInvite ? 'Welcome! Choose a password' : 'Choose a new password';
require __DIR__ . '/_layout_top.php';
?>
<div class="bg-white p-4 p-md-5 shadow-sm">
    <?php if ($user === null): ?>
        <h4 class="mb-3 text-center">Link expired</h4>
        <p class="text-muted text-center mb-4">This link is invalid, has expired, or has already been used. Ask your administrator for a new one, or request a reset below.</p>
        <a href="/admin/forgot-password" class="btn btn-outline-secondary w-100 mb-2">Request a password reset</a>
        <a href="/admin/login" class="btn btn-primary w-100">Go to sign in</a>
    <?php else: ?>
        <h4 class="mb-2 text-center"><?= htmlspecialchars($heading) ?></h4>
        <p class="text-muted text-center small mb-4"><?= htmlspecialchars($user['email']) ?></p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form action="/admin/set-password/<?= htmlspecialchars(rawurlencode($token)) ?>" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>" id="password" name="password" autocomplete="new-password" minlength="8" required autofocus>
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
                <div class="form-text">At least 8 characters.</div>
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary w-100 py-2" type="submit"><?= $isInvite ? 'Set Password &amp; Sign In' : 'Save &amp; Sign In' ?></button>
        </form>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
