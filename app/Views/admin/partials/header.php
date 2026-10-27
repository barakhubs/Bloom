<?php
/** @var string|null $title */

use App\Core\Auth;
use App\Core\Csrf;
use App\Models\Setting;

$title = $title ?? 'Admin - Bloom Beyond Borders';
$currentAdmin = Auth::user();
$logoutCsrfToken = Csrf::token();
$faviconPath = Setting::get('favicon_path', 'img/favicon.png');
$footerLogoPath = (string) Setting::get('footer_logo_path', '');

$adminNavPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
// Section heading ('' = no heading) => [href => [label, icon, required permission (null = every logged-in user)]].
// Links the current user can't access are dropped, and so are sections left empty.
$adminNavSections = array_filter(array_map(
    fn (array $links) => array_filter($links, fn (array $link) => $link[2] === null || Auth::can($link[2])),
    [
        '' => [
            '/admin' => ['Dashboard', 'fa-gauge', null],
        ],
        'Content' => [
            '/admin/story' => ['Our Story', 'fa-book-open', 'story'],
            '/admin/team' => ['Team', 'fa-people-group', 'team'],
            '/admin/partners' => ['Partners', 'fa-handshake', 'partners'],
            '/admin/gallery' => ['Gallery', 'fa-images', 'gallery'],
            '/admin/blog' => ['Blog Posts', 'fa-newspaper', 'blog'],
        ],
        'Inbox' => [
            '/admin/comments' => ['Comments', 'fa-comments', 'comments'],
            '/admin/contact' => ['Contact Messages', 'fa-envelope', 'contact'],
        ],
        'Fundraising' => [
            '/admin/donations' => ['Donations', 'fa-hand-holding-heart', 'donations'],
            '/admin/donation-schemes' => ['Donation Schemes', 'fa-bullseye', 'donations'],
        ],
        'Shop' => [
            '/admin/orders' => ['Orders', 'fa-box', 'orders'],
            '/admin/products' => ['Products', 'fa-tags', 'shop'],
            '/admin/customers' => ['Customers', 'fa-user-group', 'customers'],
        ],
        'Administration' => [
            '/admin/users' => ['Users', 'fa-users-gear', 'users'],
            '/admin/roles' => ['Roles', 'fa-user-shield', 'users'],
            '/admin/settings' => ['Site Settings', 'fa-gear', 'settings'],
        ],
        'You' => [
            '/admin/account' => ['My Account', 'fa-circle-user', null],
        ],
    ]
));
// Exact match, or a sub-page of the link ("/admin/orders/5" lights up "Orders").
$isAdminNavActive = fn (string $href) => $href === '/admin'
    ? $adminNavPath === '/admin'
    : $adminNavPath === $href || str_starts_with($adminNavPath, $href . '/');
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
        .admin-sidebar { width: 220px; min-height: calc(100vh - 56px); }
        .admin-sidebar .nav-link { color: rgba(255, 255, 255, .75); border-left: 3px solid transparent; }
        .admin-sidebar .nav-link:hover { color: #fff; }
        .admin-sidebar .nav-link.active { color: #fff; font-weight: bold; border-left-color: var(--bs-primary); background: rgba(255, 255, 255, .06); }
        .admin-sidebar .nav-link i { width: 1.25em; margin-right: .6rem; text-align: center; opacity: .85; }
        .admin-sidebar .nav-heading { color: rgba(255, 255, 255, .5); font-size: .7rem; font-weight: bold; letter-spacing: .08em; text-transform: uppercase; padding: 1rem 1.5rem .35rem; }
        .admin-sidebar .nav-heading:first-child { padding-top: .25rem; }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary px-4">
        <?php if ($footerLogoPath !== ''): ?>
            <a class="navbar-brand d-flex align-items-center" href="/admin">
                <img src="/<?= htmlspecialchars(ltrim($footerLogoPath, '/')) ?>" alt="Bloom Beyond Borders" style="height: 40px; width: auto; max-width: 220px; object-fit: contain;">
                <span class="text-white fw-bold text-uppercase ms-3">Admin</span>
            </a>
        <?php else: ?>
            <a class="navbar-brand fw-bold text-primary text-uppercase" href="/admin">Bloom Beyond Borders <span class="text-white">Admin</span></a>
        <?php endif; ?>
        <div class="ms-auto d-flex align-items-center">
            <?php if ($currentAdmin): ?>
                <a href="/admin/account" class="text-white small me-3 text-decoration-none" title="<?= htmlspecialchars($currentAdmin['email']) ?>">
                    <?= htmlspecialchars($currentAdmin['name'] ?: $currentAdmin['email']) ?>
                    <span class="opacity-75">&middot; <?= htmlspecialchars($currentAdmin['role_name']) ?></span>
                </a>
            <?php endif; ?>
            <a href="/" class="btn btn-sm btn-outline-light me-2" target="_blank">View Site</a>
            <form action="/admin/logout" method="post" class="d-inline m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($logoutCsrfToken) ?>">
                <button type="submit" class="btn btn-sm btn-primary">Logout</button>
            </form>
        </div>
    </nav>

    <div class="d-flex">
        <nav class="admin-sidebar bg-secondary flex-shrink-0 py-3">
            <div class="nav flex-column">
                <?php foreach ($adminNavSections as $heading => $links): ?>
                    <?php if ($heading !== ''): ?>
                        <div class="nav-heading"><?= htmlspecialchars((string) $heading) ?></div>
                    <?php endif; ?>
                    <?php foreach ($links as $href => [$label, $icon]): ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="nav-link px-4 py-2<?= $isAdminNavActive($href) ? ' active' : '' ?>"<?= $isAdminNavActive($href) ? ' aria-current="page"' : '' ?>>
                            <i class="fa <?= $icon ?>"></i><?= htmlspecialchars($label) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </nav>

        <div class="flex-grow-1 py-4 px-4">
