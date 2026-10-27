<?php
/** @var array $products */
/** @var string $csrfToken */

use App\Core\Money;

require __DIR__ . '/_start.php';
?>
<h2 class="h3 mb-4">My Wishlist</h2>

<?php if (empty($products)): ?>
    <div class="bg-light p-4">
        <p class="mb-3">Your wishlist is empty. Tap the <i class="fa fa-heart text-primary"></i> on any product to save it here.</p>
        <a href="/shop" class="btn btn-primary">Browse the Shop</a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-xl-4">
                <div class="donation-item h-100 p-3">
                    <a href="/shop/<?= htmlspecialchars($product['slug']) ?>" class="d-block mb-3">
                        <?php if (!empty($product['image_path'])): ?>
                            <img class="img-fluid w-100" style="height:180px;object-fit:cover;" src="/<?= htmlspecialchars(ltrim($product['image_path'], '/')) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px;"><i class="fa fa-bag-shopping fa-3x text-primary"></i></div>
                        <?php endif; ?>
                    </a>
                    <a href="/shop/<?= htmlspecialchars($product['slug']) ?>" class="h5 d-block"><?= htmlspecialchars($product['name']) ?></a>
                    <p class="mb-3"><?= htmlspecialchars(Money::format((int) $product['price_cents'])) ?></p>
                    <div class="mt-auto d-flex gap-2">
                        <?php if ((int) $product['stock'] > 0): ?>
                            <form action="/account/wishlist/<?= (int) $product['id'] ?>/move-to-cart" method="post" class="flex-fill">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-primary w-100">Move to Cart</button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="btn btn-light flex-fill" disabled>Sold Out</button>
                        <?php endif; ?>
                        <form action="/wishlist/<?= (int) $product['id'] ?>/toggle" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="return_to" value="/account/wishlist">
                            <button type="submit" class="btn btn-outline-secondary" aria-label="Remove from wishlist"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/_end.php'; ?>
