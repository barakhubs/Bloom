<?php
/** @var array $products */
/** @var string $csrfToken */
/** @var int[] $wishlistIds */
/** @var string|null $notice */

use App\Core\Money;

require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid pb-5">
    <div class="container">
        <div class="text-center mx-auto mb-5 wow fadeIn" data-wow-delay="0.1s" style="max-width: 650px;">
            <p class="section-title bg-white text-center text-primary px-3">Shop</p>
            <h2 class="display-6 mb-4">Shop With Purpose</h2>
            <p class="fs-5 mb-0">Handmade goods and Bloom Beyond Borders merchandise. Every purchase supports our programmes.</p>
        </div>

        <?php if ($notice): ?>
            <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <p class="text-center text-muted">New products are coming soon.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $i => $product): ?>
                    <?php $inStock = (int) $product['stock'] > 0; ?>
                    <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="<?= number_format(0.1 + 0.2 * ($i % 3), 1) ?>s">
                        <div class="donation-item h-100 p-4 position-relative">
                            <?php $wishlistReturnTo = '/shop'; $wishlistClass = 'position-absolute top-0 start-0 m-4'; require __DIR__ . '/_wishlist-button.php'; ?>
                            <a href="/shop/<?= htmlspecialchars($product['slug']) ?>" class="position-relative mb-4 d-block">
                                <?php if (!empty($product['image_path'])): ?>
                                    <img class="img-fluid w-100" style="height:260px;object-fit:cover;" src="/<?= htmlspecialchars(ltrim($product['image_path'], '/')) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:260px;"><i class="fa fa-bag-shopping fa-3x text-primary"></i></div>
                                <?php endif; ?>
                                <span class="btn btn-sm btn-secondary px-3 position-absolute top-0 end-0"><?= htmlspecialchars(Money::format((int) $product['price_cents'])) ?></span>
                            </a>
                            <a href="/shop/<?= htmlspecialchars($product['slug']) ?>" class="h3 d-inline-block"><?= htmlspecialchars($product['name']) ?></a>
                            <?php if (!empty($product['summary'])): ?>
                                <p><?= htmlspecialchars($product['summary']) ?></p>
                            <?php endif; ?>
                            <?php if ($inStock): ?>
                                <form action="/cart/add" method="post" class="mt-auto">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary w-100 py-3"><i class="fa fa-cart-plus me-2"></i>Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn btn-light w-100 py-3 mt-auto" disabled>Sold Out</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
