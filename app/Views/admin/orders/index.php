<?php
/** @var array $orders */
/** @var string|null $status */

use App\Core\Money;
use App\Models\Order;

$badge = ['paid' => 'bg-primary text-dark', 'shipped' => 'bg-secondary', 'pending' => 'bg-warning text-dark', 'expired' => 'bg-light text-dark border', 'cancelled' => 'bg-danger'];
$labels = ['paid' => 'Paid, to ship', 'shipped' => 'Shipped', 'pending' => 'Awaiting payment', 'expired' => 'Abandoned', 'cancelled' => 'Cancelled'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Orders</h2>
    <a href="/admin/products" class="btn btn-outline-secondary">Manage Products</a>
</div>

<ul class="nav nav-pills mb-3">
    <?php foreach (Order::STATUSES as $option): ?>
        <li class="nav-item"><a class="nav-link<?= $status === $option ? ' active' : '' ?>" href="/admin/orders?status=<?= $option ?>"><?= htmlspecialchars($labels[$option]) ?></a></li>
    <?php endforeach; ?>
    <li class="nav-item"><a class="nav-link<?= $status === null ? ' active' : '' ?>" href="/admin/orders?status=all">All</a></li>
</ul>

<div class="bg-white shadow-sm table-responsive">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Order</th>
                <th>Date</th>
                <th>Customer</th>
                <th class="text-end">Items</th>
                <th class="text-end">Total</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No orders here.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="fw-bold text-nowrap"><?= htmlspecialchars($order['reference']) ?></td>
                    <td class="small text-nowrap"><?= htmlspecialchars(date('j M Y, H:i', strtotime((string) ($order['paid_at'] ?? $order['created_at'])))) ?></td>
                    <td>
                        <?= htmlspecialchars($order['customer_name'] ?: '—') ?>
                        <?php if (!empty($order['customer_email'])): ?><div class="small text-muted"><?= htmlspecialchars($order['customer_email']) ?></div><?php endif; ?>
                    </td>
                    <td class="text-end"><?= (int) $order['item_count'] ?></td>
                    <td class="text-end"><?= htmlspecialchars(Money::format((int) $order['total_cents'])) ?></td>
                    <td><span class="badge <?= $badge[$order['status']] ?? 'bg-secondary' ?>"><?= htmlspecialchars($labels[$order['status']] ?? $order['status']) ?></span></td>
                    <td class="text-end"><a href="/admin/orders/<?= (int) $order['id'] ?>" class="btn btn-sm btn-secondary">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="small text-muted mt-2">Showing the latest 500.</p>
