<?php
/** @var array $customer */
/** @var array $orders */
/** @var array $donations */
/** @var int|null $wishlistCount */

use App\Core\Money;

require __DIR__ . '/_start.php';
?>
<h2 class="h3 mb-4">Welcome, <?= htmlspecialchars(strtok($customer['name'], ' ') ?: $customer['name']) ?></h2>

<div class="row g-4">
    <div class="col-md-6">
        <div class="bg-light p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5 mb-0">Recent Orders</h3>
                <a href="/account/orders" class="small">View all</a>
            </div>
            <?php if (empty($orders)): ?>
                <p class="text-muted mb-0">No orders yet.</p>
            <?php endif; ?>
            <?php foreach ($orders as $order): ?>
                <a href="/account/orders/<?= htmlspecialchars(rawurlencode($order['reference'])) ?>" class="d-flex justify-content-between text-dark text-decoration-none border-bottom py-2">
                    <span><?= htmlspecialchars($order['reference']) ?> <span class="small text-muted">· <?= htmlspecialchars(ucfirst($order['status'])) ?></span></span>
                    <span><?= htmlspecialchars(Money::format((int) $order['total_cents'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="bg-light p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5 mb-0">Recent Donations</h3>
                <a href="/account/donations" class="small">View all</a>
            </div>
            <?php if (empty($donations)): ?>
                <p class="text-muted mb-2">No donations yet.</p>
                <a href="/donate" class="btn btn-sm btn-primary">Make a Donation</a>
            <?php endif; ?>
            <?php foreach ($donations as $donation): ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span><?= htmlspecialchars($donation['scheme_name'] ?? 'General donation') ?> <span class="small text-muted">· <?= htmlspecialchars(date('j M Y', strtotime((string) $donation['paid_at']))) ?></span></span>
                    <span><?= htmlspecialchars(Money::format((int) $donation['amount_cents'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if ($wishlistCount !== null): ?>
        <div class="col-md-6">
            <a href="/account/wishlist" class="d-block bg-light p-4 text-dark text-decoration-none">
                <h3 class="h5 mb-1">Wishlist</h3>
                <p class="mb-0 text-muted"><?= $wishlistCount ?> saved item<?= $wishlistCount === 1 ? '' : 's' ?></p>
            </a>
        </div>
    <?php endif; ?>
    <div class="col-md-6">
        <a href="/account/profile" class="d-block bg-light p-4 text-dark text-decoration-none">
            <h3 class="h5 mb-1">Profile &amp; Address</h3>
            <p class="mb-0 text-muted"><?= !empty($customer['ship_line1']) ? 'Saved address will be pre-filled at checkout.' : 'Save a shipping address for faster checkout.' ?></p>
        </a>
    </div>
</div>
<?php require __DIR__ . '/_end.php'; ?>
