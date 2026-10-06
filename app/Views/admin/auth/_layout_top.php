<?php
/** @var string $title */

use App\Models\Setting;

$faviconPath = Setting::get('favicon_path', 'img/favicon.png');
$siteLogoPath = Setting::get('site_logo_path', '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="robots" content="noindex">
    <link href="/<?= htmlspecialchars(ltrim($faviconPath, '/')) ?>" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container" style="max-width: 420px;">
        <div class="text-center mb-4">
            <?php if (!empty($siteLogoPath)): ?>
                <img src="/<?= htmlspecialchars(ltrim($siteLogoPath, '/')) ?>" alt="Bloom Beyond Borders" class="site-logo mb-2" style="height: 70px; width: auto; max-width: 100%; object-fit: contain;">
            <?php else: ?>
                <h1 class="site-title text-uppercase fw-bold text-primary m-0">Bloom Beyond Borders</h1>
            <?php endif; ?>
            <span class="text-muted d-block">Admin</span>
        </div>
