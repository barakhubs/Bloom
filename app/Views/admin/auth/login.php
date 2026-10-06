<?php
/** @var string $title */
/** @var array $errors */
/** @var array $old */
/** @var string $csrfToken */

require __DIR__ . '/_layout_top.php';
?>
<div class="bg-white p-4 p-md-5 shadow-sm">
    <h4 class="mb-4 text-center">Sign In</h4>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form action="/admin/login" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="mb-4">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label">Password</label>
                <a href="/admin/forgot-password" class="small">Forgot password?</a>
            </div>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit">Sign In</button>
    </form>
</div>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
