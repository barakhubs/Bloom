<?php
/** @var array $products */
/** @var bool $shopEnabled */
/** @var string $csrfToken */

use App\Core\Money;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Products</h2>
    <a href="/admin/products/create" class="btn btn-primary">Add Product</a>
</div>

<?php if (!$shopEnabled): ?>
    <div class="alert alert-warning">The shop is currently <strong>hidden</strong> from the public site. Turn it on under <a href="/admin/settings">Settings &rarr; Shop</a> when your products are ready.</div>
<?php endif; ?>

<div class="bg-white shadow-sm table-responsive">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th class="text-end">Price</th>
                <th class="text-end">Stock</th>
                <th>Status</th>
                <th>Sort</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No products yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php if (!empty($product['image_path'])): ?><img src="/<?= htmlspecialchars(ltrim($product['image_path'], '/')) ?>" alt="" style="height:40px;width:60px;object-fit:cover;"><?php else: ?><span class="text-muted small">No image</span><?php endif; ?></td>
                    <td>
                        <?= htmlspecialchars($product['name']) ?>
                        <div class="small text-muted">/shop/<?= htmlspecialchars($product['slug']) ?></div>
                    </td>
                    <td class="text-end"><?= htmlspecialchars(Money::format((int) $product['price_cents'])) ?></td>
                    <td class="text-end<?= (int) $product['stock'] === 0 ? ' text-danger fw-bold' : '' ?>"><?= (int) $product['stock'] ?></td>
                    <td>
                        <?php if ((int) $product['is_active'] === 1): ?>
                            <span class="badge bg-primary text-dark">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $product['sort_order'] ?></td>
                    <td class="text-end text-nowrap">
                        <a href="/admin/products/<?= (int) $product['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="/admin/products/<?= (int) $product['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this product? Past orders keep their line items.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
