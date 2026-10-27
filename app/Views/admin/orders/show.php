<?php
/** @var array $order */
/** @var string|null $flash */
/** @var bool $testMode */
/** @var string $csrfToken */

use App\Core\Money;

$stripeUrl = !empty($order['stripe_payment_intent_id'])
    ? 'https://dashboard.stripe.com/' . ($testMode ? 'test/' : '') . 'payments/' . $order['stripe_payment_intent_id']
    : null;
$cityLine = trim(implode(' ', array_filter([$order['ship_city'], $order['ship_state'], $order['ship_postal_code']])));
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Order <?= htmlspecialchars($order['reference']) ?></h2>
    <a href="/admin/orders" class="btn btn-outline-secondary">&larr; All Orders</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white p-4 shadow-sm mb-4">
            <h5 class="mb-3">Items</h5>
            <table class="table mb-0">
                <thead><tr><th>Product</th><th class="text-end">Price</th><th class="text-end">Qty</th><th class="text-end">Line Total</th></tr></thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?><?php if ($item['product_id'] === null): ?> <span class="small text-muted">(product deleted)</span><?php endif; ?></td>
                            <td class="text-end"><?= htmlspecialchars(Money::format((int) $item['unit_price_cents'])) ?></td>
                            <td class="text-end"><?= (int) $item['quantity'] ?></td>
                            <td class="text-end"><?= htmlspecialchars(Money::format((int) $item['unit_price_cents'] * (int) $item['quantity'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end"><?= htmlspecialchars(Money::format((int) $order['subtotal_cents'])) ?></td></tr>
                    <tr><td colspan="3" class="text-end">Shipping</td><td class="text-end"><?= htmlspecialchars(Money::format((int) $order['shipping_cents'])) ?></td></tr>
                    <tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end"><?= htmlspecialchars(Money::format((int) $order['total_cents'])) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white p-4 shadow-sm mb-4">
            <h5 class="mb-3">Status: <?= htmlspecialchars(ucfirst($order['status'])) ?></h5>
            <p class="small mb-1">Placed <?= htmlspecialchars(date('j M Y, H:i', strtotime((string) $order['created_at']))) ?></p>
            <?php if ($order['paid_at']): ?><p class="small mb-1">Paid <?= htmlspecialchars(date('j M Y, H:i', strtotime((string) $order['paid_at']))) ?></p><?php endif; ?>
            <?php if ($order['shipped_at']): ?><p class="small mb-1">Shipped <?= htmlspecialchars(date('j M Y, H:i', strtotime((string) $order['shipped_at']))) ?></p><?php endif; ?>
            <?php if ($stripeUrl): ?><p class="small mb-3"><a href="<?= htmlspecialchars($stripeUrl) ?>" target="_blank" rel="noopener">View payment in Stripe</a></p><?php endif; ?>

            <?php if ($order['status'] === 'paid'): ?>
                <form action="/admin/orders/<?= (int) $order['id'] ?>/ship" method="post" class="mb-2" onsubmit="return confirm('Mark as shipped and email the customer?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <button type="submit" class="btn btn-primary w-100">Mark as Shipped</button>
                </form>
            <?php endif; ?>
            <?php if (in_array($order['status'], ['paid', 'shipped'], true)): ?>
                <form action="/admin/orders/<?= (int) $order['id'] ?>/cancel" method="post" onsubmit="return confirm('Cancel this order? Issue the refund in Stripe separately.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="form-check small mb-2">
                        <input class="form-check-input" type="checkbox" name="restock" id="restock" value="1" checked>
                        <label class="form-check-label" for="restock">Put items back in stock</label>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100">Cancel Order</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="bg-white p-4 shadow-sm">
            <h5 class="mb-3">Customer
                <?php if (!empty($order['customer_id'])): ?>
                    <?php if (\App\Core\Auth::can('customers')): ?>
                        <a href="/admin/customers/<?= (int) $order['customer_id'] ?>" class="badge bg-secondary text-decoration-none align-middle ms-1">Account</a>
                    <?php else: ?>
                        <span class="badge bg-secondary align-middle ms-1">Account</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge bg-light text-dark border align-middle ms-1">Guest</span>
                <?php endif; ?>
            </h5>
            <p class="mb-1"><?= htmlspecialchars($order['customer_name'] ?: '—') ?></p>
            <?php if ($order['customer_email']): ?><p class="mb-1"><a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>"><?= htmlspecialchars($order['customer_email']) ?></a></p><?php endif; ?>
            <?php if ($order['customer_phone']): ?><p class="mb-3"><?= htmlspecialchars($order['customer_phone']) ?></p><?php endif; ?>

            <h6 class="mt-3">Ship To</h6>
            <?php if ($order['ship_line1']): ?>
                <address class="mb-0">
                    <?= htmlspecialchars((string) $order['ship_name']) ?><br>
                    <?= htmlspecialchars($order['ship_line1']) ?><br>
                    <?php if ($order['ship_line2']): ?><?= htmlspecialchars($order['ship_line2']) ?><br><?php endif; ?>
                    <?php if ($cityLine !== ''): ?><?= htmlspecialchars($cityLine) ?><br><?php endif; ?>
                    <?= htmlspecialchars((string) $order['ship_country']) ?>
                </address>
            <?php else: ?>
                <p class="text-muted small mb-0">Collected by Stripe once the order is paid.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
