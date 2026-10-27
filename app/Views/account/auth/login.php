<?php
/** @var array $errors */
/** @var array $old */
/** @var string $next */
/** @var string|null $flash */
/** @var string $csrfToken */
require dirname(__DIR__, 2) . '/partials/page-header.php';
?>

<div class="container-fluid pb-5">
    <div class="container" style="max-width: 520px;">
        <?php if ($flash): ?>
            <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <div class="bg-light p-4 p-md-5">
            <h2 class="h3 mb-4">Sign in to your account</h2>
            <form action="/account/login" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" autocomplete="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                    <label for="email">Email</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
                    <label for="password">Password</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-3">Sign In</button>
            </form>
            <div class="d-flex justify-content-between mt-3 small">
                <a href="/account/forgot">Forgot your password?</a>
                <a href="/account/register">Create an account</a>
            </div>
        </div>
        <p class="small text-muted text-center mt-3 mb-0">An account is optional. You can always shop and donate as a guest.</p>
    </div>
</div>
