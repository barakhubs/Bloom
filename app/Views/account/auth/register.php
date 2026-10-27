<?php
/** @var array $errors */
/** @var array $old */
/** @var string $csrfToken */
require dirname(__DIR__, 2) . '/partials/page-header.php';
?>

<div class="container-fluid pb-5">
    <div class="container" style="max-width: 560px;">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <div class="bg-light p-4 p-md-5">
            <h2 class="h3 mb-2">Create an account</h2>
            <p class="mb-4">Track your orders and donations, save a wishlist, and check out faster with a saved address.</p>
            <form action="/account/register" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <!-- Honeypot: real users never see or fill this; simple bots that skip CSS often do -->
                <div class="d-none" aria-hidden="true">
                    <label for="register-website">Website</label>
                    <input type="text" id="register-website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="name" name="name" placeholder="Name" autocomplete="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                    <label for="name">Your Name</label>
                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="email" name="email" placeholder="Email" autocomplete="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                    <label for="email">Email</label>
                    <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>" id="password" name="password" placeholder="Password" autocomplete="new-password">
                    <label for="password">Password (at least 8 characters)</label>
                    <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" autocomplete="new-password">
                    <label for="password_confirmation">Confirm Password</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-3">Create Account</button>
            </form>
            <p class="small mt-3 mb-0">Already have an account? <a href="/account/login">Sign in</a></p>
        </div>
    </div>
</div>
