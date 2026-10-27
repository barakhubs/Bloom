<?php
/** @var array $order */
/** @var string $csrfToken */

use App\Core\Money;

require __DIR__ . '/_start.php';

$statusText = [
    'paid' => 'Paid, being prepared for shipping',
    'shipped' => 'Shipped' . ($order['shipped_at'] ? ' on ' . date('j M Y', strtotime((string) $order['shipped_at'])) : ''),
    'cancelled' => 'Cancelled',
][$order['status']] ?? ucfirst($order['status']);
$cityLine = trim(implode(' ', array_filter([$order['ship_city'], $order['ship_state'], $order['ship_postal_code']])));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h2 class="h3 mb-0">Order <?= htmlspecialchars($order['reference']) ?></h2>
    <a href="/account/orders" class="small">&larr; All orders</a>
</div>

<p class="mb-1"><strong>Status:</strong> <?= htmlspecialchars($statusText) ?></p>
<p class="text-muted small mb-4">Placed <?= htmlspecialchars(date('j M Y', strtotime((string) $order['created_at']))) ?></p>

<div class="row g-4">
    <div class="col-md-7">
        <div class="bg-light p-4">
            <?php foreach ($order['items'] as $item): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?= (int) $item['quantity'] ?> &times; <?= htmlspecialchars($item['product_name']) ?></span>
                    <span><?= htmlspecialchars(Money::format((int) $item['unit_price_cents'] * (int) $item['quantity'])) ?></span>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><span><?= htmlspecialchars(Money::format((int) $order['subtotal_cents'])) ?></span></div>
            <div class="d-flex justify-content-between mb-1"><span>Shipping</span><span><?= htmlspecialchars(Money::format((int) $order['shipping_cents'])) ?></span></div>
            <div class="d-flex justify-content-between fw-bold"><span>Total</span><span><?= htmlspecialchars(Money::format((int) $order['total_cents'])) ?></span></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="bg-light p-4">
            <h3 class="h6">Shipping to</h3>
            <?php if ($order['ship_line1']): ?>
                <address class="mb-0">
                    <?= htmlspecialchars((string) $order['ship_name']) ?><br>
                    <?= htmlspecialchars($order['ship_line1']) ?><br>
                    <?php if ($order['ship_line2']): ?><?= htmlspecialchars($order['ship_line2']) ?><br><?php endif; ?>
                    <?php if ($cityLine !== ''): ?><?= htmlspecialchars($cityLine) ?><br><?php endif; ?>
                    <?= htmlspecialchars((string) $order['ship_country']) ?>
                </address>
            <?php else: ?>
                <p class="text-muted mb-0">&mdash;</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="mt-4 d-flex flex-wrap gap-2">
    <?php if (!empty($order['customer_email'])): ?>
        <form action="/account/orders/<?= htmlspecialchars(rawurlencode($order['reference'])) ?>/resend" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <button type="submit" class="btn btn-outline-secondary">Email Me the Confirmation Again</button>
        </form>
    <?php endif; ?>
    <a href="/contact" class="btn btn-link">Need to change or cancel? Contact us</a>
</div>
<?php require __DIR__ . '/_end.php'; ?>
