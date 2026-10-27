<?php
/** @var string $token */
/** @var bool $valid */
/** @var array $errors */
/** @var string $csrfToken */
require dirname(__DIR__, 2) . '/partials/page-header.php';
?>

<div class="container-fluid pb-5">
    <div class="container" style="max-width: 520px;">
        <div class="bg-light p-4 p-md-5">
            <?php if (!$valid): ?>
                <h2 class="h3 mb-3">Link expired</h2>
                <p>This reset link is invalid, has expired, or has already been used.</p>
                <a href="/account/forgot" class="btn btn-primary py-3 px-4">Request a New Link</a>
            <?php else: ?>
                <h2 class="h3 mb-4">Choose a new password</h2>
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
                <?php endif; ?>
                <form action="/account/reset/<?= htmlspecialchars(rawurlencode($token)) ?>" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>" id="password" name="password" placeholder="New password" autocomplete="new-password">
                        <label for="password">New Password (at least 8 characters)</label>
                        <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm" autocomplete="new-password">
                        <label for="password_confirmation">Confirm New Password</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3">Save Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
