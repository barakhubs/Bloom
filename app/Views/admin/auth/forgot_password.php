<?php
/** @var string $title */
/** @var bool $sent */
/** @var string $csrfToken */

require __DIR__ . '/_layout_top.php';
?>
<div class="bg-white p-4 p-md-5 shadow-sm">
    <h4 class="mb-3 text-center">Forgot password</h4>

    <?php if ($sent): ?>
        <div class="alert alert-success" role="alert">
            If that email belongs to an active admin account, a reset link is on its way. It expires in 2 hours.
        </div>
        <a href="/admin/login" class="btn btn-primary w-100">Back to sign in</a>
    <?php else: ?>
        <p class="text-muted small mb-4">Enter your email and we'll send you a link to choose a new password.</p>
        <form action="/admin/forgot-password" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>
            <button class="btn btn-primary w-100 py-2" type="submit">Send Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="/admin/login" class="small">Back to sign in</a>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
