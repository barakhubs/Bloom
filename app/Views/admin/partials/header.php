<?php
/** @var string|null $title */

use App\Core\Auth;
use App\Core\Csrf;
use App\Models\AdminUser;
use App\Models\Setting;

$title = $title ?? 'Admin - Bloom Beyond Borders';
$currentAdmin = Auth::check() ? (new AdminUser())->findById(Auth::id()) : null;
$logoutCsrfToken = Csrf::token();
$faviconPath = Setting::get('favicon_path', 'img/favicon.ico');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/<?= htmlspecialchars(ltrim($faviconPath, '/')) ?>" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@600;700&family=Open+Sans&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <style>
        body { background: #f5f5f0; }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary px-4">
        <a class="navbar-brand fw-bold text-primary text-uppercase" href="/admin">Bloom Beyond Borders <span class="text-white">Admin</span></a>
        <div class="ms-auto d-flex align-items-center">
            <?php if ($currentAdmin): ?>
                <span class="text-white small me-3"><?= htmlspecialchars($currentAdmin['email']) ?></span>
            <?php endif; ?>
            <a href="/" class="btn btn-sm btn-outline-light me-2" target="_blank">View Site</a>
            <form action="/admin/logout" method="post" class="d-inline m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($logoutCsrfToken) ?>">
                <button type="submit" class="btn btn-sm btn-primary">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container-fluid py-4 px-4">
