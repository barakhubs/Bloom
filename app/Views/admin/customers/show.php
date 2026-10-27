<?php
/** @var array $customer */
/** @var array $orders */
/** @var array $donations */
/** @var string|null $flash */
/** @var string $csrfToken */

use App\Core\Auth;
use App\Core\Money;

$address = array_filter([
    $customer['ship_line1'],
    $customer['ship_line2'],
    trim(implode(' ', array_filter([$customer['ship_city'], $customer['ship_state'], $customer['ship_postal_code']]))),
    $customer['ship_country'],
]);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><?= htmlspecialchars($customer['name']) ?></h2>
    <a href="/admin/customers" class="btn btn-outline-secondary">&larr; All Customers</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="bg-white p-4 shadow-sm mb-4">
            <p class="mb-1"><a href="mailto:<?= htmlspecialchars($customer['email']) ?>"><?= htmlspecialchars($customer['email']) ?></a></p>
            <p class="small mb-1"><?= $customer['email_verified_at'] !== null ? 'Email confirmed ' . htmlspecialchars(date('j M Y', strtotime((string) $customer['email_verified_at']))) : '<span class="text-warning">Email not confirmed</span>' ?></p>
            <?php if ($customer['phone']): ?><p class="small mb-1"><?= htmlspecialchars($customer['phone']) ?></p><?php endif; ?>
            <p class="small text-muted mb-1">Joined <?= htmlspecialchars(date('j M Y', strtotime((string) $customer['created_at']))) ?></p>
            <p class="small text-muted mb-3">Last sign-in <?= $customer['last_login_at'] ? htmlspecialchars(date('j M Y, H:i', strtotime((string) $customer['last_login_at']))) : 'never' ?></p>
            <?php if (!empty($address)): ?>
                <h6>Saved Address</h6>
                <address class="small"><?= implode('<br>', array_map('htmlspecialchars', $address)) ?></address>
            <?php endif; ?>

            <form action="/admin/customers/<?= (int) $customer['id'] ?>/send-reset" method="post" class="mb-2">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-secondary w-100">Email a Password-Reset Link</button>
            </form>
            <form action="/admin/customers/<?= (int) $customer['id'] ?>/delete" method="post" onsubmit="return confirm('Delete this customer account? Their orders and donations are kept but unlinked.');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-outline-danger w-100">Delete Account</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="bg-white p-4 shadow-sm mb-4">
            <h5 class="mb-3">Orders</h5>
            <?php if (empty($orders)): ?><p class="text-muted mb-0">No orders.</p><?php endif; ?>
            <?php foreach ($orders as $order): ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>
                        <?php if (Auth::can('orders')): ?><a href="/admin/orders/<?= (int) $order['id'] ?>"><?= htmlspecialchars($order['reference']) ?></a><?php else: ?><?= htmlspecialchars($order['reference']) ?><?php endif; ?>
                        <span class="small text-muted">· <?= htmlspecialchars(ucfirst($order['status'])) ?> · <?= htmlspecialchars(date('j M Y', strtotime((string) $order['created_at']))) ?></span>
                    </span>
                    <span><?= htmlspecialchars(Money::format((int) $order['total_cents'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="bg-white p-4 shadow-sm">
            <h5 class="mb-3">Donations</h5>
            <?php if (empty($donations)): ?><p class="text-muted mb-0">No donations.</p><?php endif; ?>
            <?php foreach ($donations as $donation): ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span><?= htmlspecialchars($donation['scheme_name'] ?? 'General') ?> <span class="small text-muted">· <?= htmlspecialchars(date('j M Y', strtotime((string) $donation['paid_at']))) ?></span></span>
                    <span><?= htmlspecialchars(Money::format((int) $donation['amount_cents'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
