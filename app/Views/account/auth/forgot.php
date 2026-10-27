<?php
/** @var bool $sent */
/** @var string $csrfToken */
require dirname(__DIR__, 2) . '/partials/page-header.php';
?>

<div class="container-fluid pb-5">
    <div class="container" style="max-width: 520px;">
        <div class="bg-light p-4 p-md-5">
            <?php if ($sent): ?>
                <h2 class="h3 mb-3">Check your email</h2>
                <p class="mb-0">If an account exists for that address, we've sent a link to reset your password. It expires in 2 hours.</p>
            <?php else: ?>
                <h2 class="h3 mb-3">Forgot your password?</h2>
                <p>Enter your email and we'll send you a link to choose a new one.</p>
                <form action="/account/forgot" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="d-none" aria-hidden="true">
                        <label for="forgot-website">Website</label>
                        <input type="text" id="forgot-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" autocomplete="email" required>
                        <label for="email">Email</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3">Send Reset Link</button>
                </form>
            <?php endif; ?>
            <p class="small mt-3 mb-0"><a href="/account/login">&larr; Back to sign in</a></p>
        </div>
    </div>
</div>
