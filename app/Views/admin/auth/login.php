<?php
/** @var array $errors */
/** @var array $old */
/** @var string $csrfToken */

use App\Models\Setting;

$faviconPath = Setting::get('favicon_path', 'img/favicon.png');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/<?= htmlspecialchars(ltrim($faviconPath, '/')) ?>" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container" style="max-width: 420px;">
        <div class="text-center mb-4">
            <h1 class="site-title text-uppercase fw-bold text-primary m-0">Bloom Beyond Borders</h1>
            <span class="text-muted">Admin</span>
        </div>

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
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Sign In</button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="/" class="text-muted small"><i class="fa fa-arrow-left me-1"></i>Back to site</a>
        </div>
    </div>
</body>

</html>
