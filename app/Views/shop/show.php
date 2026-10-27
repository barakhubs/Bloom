<?php
/** @var array $product */
/** @var string $csrfToken */
/** @var int[] $wishlistIds */
/** @var string|null $notice */

use App\Core\Cart;
use App\Core\Money;

require dirname(__DIR__) . '/partials/page-header.php';

$stock = (int) $product['stock'];
$maxQty = min($stock, Cart::MAX_QTY);
?>

<div class="container-fluid pb-5">
    <div class="container">
        <?php if ($notice): ?>
            <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
        <?php endif; ?>

        <div class="row g-5">
            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                <?php if (!empty($product['image_path'])): ?>
                    <img class="img-fluid w-100" style="max-height:520px;object-fit:cover;" src="/<?= htmlspecialchars(ltrim($product['image_path'], '/')) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:420px;"><i class="fa fa-bag-shopping fa-5x text-primary"></i></div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.3s">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <h2 class="display-6 mb-2"><?= htmlspecialchars($product['name']) ?></h2>
                    <?php $wishlistReturnTo = '/shop/' . $product['slug']; $wishlistClass = 'flex-shrink-0'; require __DIR__ . '/_wishlist-button.php'; ?>
                </div>
                <p class="h3 text-primary mb-4"><?= htmlspecialchars(Money::format((int) $product['price_cents'])) ?></p>
                <?php if (!empty($product['summary'])): ?>
                    <p class="fs-5"><?= htmlspecialchars($product['summary']) ?></p>
                <?php endif; ?>
                <?php if (!empty($product['description'])): ?>
                    <?php foreach (preg_split('/\R\s*\R/', trim((string) $product['description'])) as $paragraph): ?>
                        <p><?= nl2br(htmlspecialchars(trim($paragraph))) ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($stock > 0): ?>
                    <form action="/cart/add" method="post" class="row g-2 align-items-end mt-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <div class="col-4 col-md-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?= $maxQty ?>">
                        </div>
                        <div class="col-8 col-md-9">
                            <button type="submit" class="btn btn-primary py-3 w-100"><i class="fa fa-cart-plus me-2"></i>Add to Cart</button>
                        </div>
                        <?php if ($stock <= 5): ?>
                            <div class="col-12 small text-muted">Only <?= $stock ?> left.</div>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-light py-3 px-5 mt-4" disabled>Sold Out</button>
                <?php endif; ?>

                <a href="/shop" class="d-inline-block mt-4">&larr; Back to the shop</a>
            </div>
        </div>
    </div>
</div>
