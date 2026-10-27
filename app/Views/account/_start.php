<?php
/**
 * Opens the account-area layout: page header, left nav, flash message, and the
 * "confirm your email" banner. Close with _end.php.
 *
 * @var array $customer
 * @var string|null $flash
 * @var string $csrfToken
 */

use App\Core\Shop;

require dirname(__DIR__) . '/partials/page-header.php';

$accountPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/account', PHP_URL_PATH) ?: '/account', '/');
$accountLinks = array_filter([
    '/account' => ['Overview', 'fa-house'],
    '/account/orders' => Shop::enabled() || $accountPath === '/account/orders' ? ['Orders', 'fa-box'] : null,
    '/account/donations' => ['Donations', 'fa-heart'],
    '/account/wishlist' => Shop::enabled() ? ['Wishlist', 'fa-bookmark'] : null,
    '/account/profile' => ['Profile', 'fa-user'],
]);
?>
<div class="container-fluid pb-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="bg-light p-3">
                    <p class="fw-bold mb-1 px-2"><?= htmlspecialchars($customer['name']) ?></p>
                    <p class="small text-muted mb-3 px-2 text-break"><?= htmlspecialchars($customer['email']) ?></p>
                    <nav class="nav flex-column">
                        <?php foreach ($accountLinks as $href => [$label, $icon]): ?>
                            <?php $active = $accountPath === $href || ($href !== '/account' && str_starts_with($accountPath, $href . '/')); ?>
                            <a class="nav-link<?= $active ? ' fw-bold text-secondary' : ' text-dark' ?>" href="<?= $href ?>"><i class="fa <?= $icon ?> fa-fw me-2 text-primary"></i><?= $label ?></a>
                        <?php endforeach; ?>
                    </nav>
                    <form action="/account/logout" method="post" class="mt-2 px-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn btn-link p-0 text-dark"><i class="fa fa-right-from-bracket fa-fw me-2 text-primary"></i>Sign Out</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-9">
                <?php if (!empty($flash)): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>
                <?php if ($customer['email_verified_at'] === null): ?>
                    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span>Please confirm your email address. Earlier guest orders and donations made with it will appear here once you do.</span>
                        <form action="/account/verify/resend" method="post" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-secondary">Resend Link</button>
                        </form>
                    </div>
                <?php endif; ?>
