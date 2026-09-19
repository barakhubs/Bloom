<?php
/** @var string|null $title */
/** @var string|null $description */
/** @var string|null $ogImage */
/** @var bool|null $noindex */

use App\Models\Setting;

$title = $title ?? 'Bloom Beyond Borders';
$description = $description ?? 'Bloom Beyond Borders supports vulnerable children and women in Uganda through education, healthcare, and economic opportunity, and helps African immigrant families in the US integrate with culturally responsive guidance.';
$ogImage = $ogImage ?? '/img/10.jpeg';
$noindex = $noindex ?? false;
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$currentPath = rtrim($currentPath, '/');
if ($currentPath === '') {
    $currentPath = '/';
}

$appConfig = require dirname(__DIR__, 2) . '/Config/app.php';
$canonicalUrl = rtrim($appConfig['base_url'], '/') . $currentPath;

$settings = Setting::all();

$navLinks = [
    '/' => 'Home',
    '/our-story' => 'Our Story',
    '/our-services' => 'Our Services',
    '/our-team' => 'Our Team',
    '/partners' => 'Partners',
    '/gallery' => 'Gallery',
    '/blog' => 'Blog',
    '/contact' => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    <?php if ($noindex): ?>
        <meta name="robots" content="noindex, follow">
    <?php endif; ?>

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:image" content="<?= htmlspecialchars(rtrim($appConfig['base_url'], '/') . $ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars(rtrim($appConfig['base_url'], '/') . $ogImage) ?>">

    <link href="/<?= htmlspecialchars(ltrim($settings['favicon_path'] ?: 'img/favicon.png', '/')) ?>" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="/lib/animate/animate.min.css" rel="stylesheet">
    <link href="/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>
    <!-- Spinner End -->


    <!-- Topbar Start -->
    <div class="container-fluid bg-white top-bar wow fadeIn" data-wow-delay="0.1s">
        <div class="row align-items-center h-100">
            <div class="col-lg-3 text-center text-lg-start">
                <a href="/">
                    <?php if (!empty($settings['site_logo_path'])): ?>
                        <img src="/<?= htmlspecialchars(ltrim($settings['site_logo_path'], '/')) ?>" alt="Bloom Beyond Borders" class="site-logo">
                    <?php else: ?>
                        <h1 class="site-title text-uppercase fw-bold text-primary m-0">Bloom Beyond Borders</h1>
                    <?php endif; ?>
                </a>
            </div>
            <div class="col-lg-9 d-none d-lg-block">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-end">
                            <div class="flex-shrink-0 btn-square bg-primary">
                                <i class="fa fa-phone-alt text-dark"></i>
                            </div>
                            <div class="ms-2">
                                <h6 class="text-primary mb-0">Call Us</h6>
                                <span class="text-dark"><?= htmlspecialchars($settings['contact_phone'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-end">
                            <div class="flex-shrink-0 btn-square bg-primary">
                                <i class="fa fa-envelope-open text-dark"></i>
                            </div>
                            <div class="ms-2">
                                <h6 class="text-primary mb-0">Mail Us</h6>
                                <span class="text-dark"><?= htmlspecialchars($settings['contact_email'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-end">
                            <div class="flex-shrink-0 btn-square bg-primary">
                                <i class="fa fa-map-marker-alt text-dark"></i>
                            </div>
                            <div class="ms-2">
                                <h6 class="text-primary mb-0">Address</h6>
                                <span class="text-dark"><?= htmlspecialchars($settings['contact_address'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar Start -->
    <div class="container-fluid bg-secondary px-0 wow fadeIn" data-wow-delay="0.1s">
        <div class="nav-bar">
            <nav class="navbar navbar-expand-lg bg-primary navbar-dark px-4 py-lg-0">
                <h4 class="d-lg-none m-0">Menu</h4>
                <button type="button" class="navbar-toggler me-0" data-bs-toggle="offcanvas" data-bs-target="#mobileNavOffcanvas" aria-controls="mobileNavOffcanvas" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Desktop nav (lg and up); mobile uses the offcanvas panel below instead -->
                <div class="d-none d-lg-flex align-items-center flex-grow-1">
                    <div class="navbar-nav">
                        <?php foreach ($navLinks as $href => $label): ?>
                            <a href="<?= htmlspecialchars($href) ?>" class="nav-item nav-link<?= $currentPath === $href ? ' active' : '' ?>"><?= htmlspecialchars($label) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Mobile Off-canvas Nav Start -->
    <div class="offcanvas offcanvas-start bg-secondary d-lg-none" tabindex="-1" id="mobileNavOffcanvas" aria-labelledby="mobileNavOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-primary text-uppercase fw-bold" id="mobileNavOffcanvasLabel">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <div class="navbar-nav">
                <?php foreach ($navLinks as $href => $label): ?>
                    <a href="<?= htmlspecialchars($href) ?>" class="nav-item nav-link<?= $currentPath === $href ? ' active' : '' ?>"><?= htmlspecialchars($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- Mobile Off-canvas Nav End -->
