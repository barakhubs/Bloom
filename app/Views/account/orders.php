<?php
/** @var array $orders */

use App\Core\Money;

require __DIR__ . '/_start.php';
?>
<h2 class="h3 mb-4">My Orders</h2>

<?php if (empty($orders)): ?>
    <div class="bg-light p-4">
        <p class="mb-3">You haven't placed any orders yet.</p>
        <a href="/shop" class="btn btn-primary">Visit the Shop</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Order</th><th>Date</th><th class="text-end">Items</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($order['reference']) ?></td>
                        <td><?= htmlspecialchars(date('j M Y', strtotime((string) ($order['paid_at'] ?? $order['created_at'])))) ?></td>
                        <td class="text-end"><?= (int) $order['item_count'] ?></td>
                        <td class="text-end"><?= htmlspecialchars(Money::format((int) $order['total_cents'])) ?></td>
                        <td><?= htmlspecialchars(['paid' => 'Processing', 'shipped' => 'Shipped', 'cancelled' => 'Cancelled'][$order['status']] ?? ucfirst($order['status'])) ?></td>
                        <td class="text-end"><a href="/account/orders/<?= htmlspecialchars(rawurlencode($order['reference'])) ?>" class="btn btn-sm btn-secondary">Details</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/_end.php'; ?>
