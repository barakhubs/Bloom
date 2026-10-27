<?php
/** @var array $cart */
/** @var int $shippingCents */
/** @var int $totalCents */
/** @var string|null $notice */
/** @var string $csrfToken */
/** @var bool $paymentsEnabled */

use App\Core\Cart;
use App\Core\Money;

require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid pb-5">
    <div class="container">
        <?php if ($notice): ?>
            <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
        <?php endif; ?>

        <?php if (empty($cart['lines'])): ?>
            <div class="text-center py-5">
                <i class="fa fa-cart-shopping fa-4x text-primary mb-4"></i>
                <h2 class="display-6 mb-4">Your Cart Is Empty</h2>
                <a href="/shop" class="btn btn-primary py-3 px-4">Browse the Shop</a>
            </div>
        <?php else: ?>
            <div class="row g-5">
                <div class="col-lg-8">
                    <form action="/cart/update" method="post" id="cart-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Price</th>
                                        <th style="width:110px;">Qty</th>
                                        <th class="text-end">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart['lines'] as $line): ?>
                                        <?php $product = $line['product']; ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($product['image_path'])): ?>
                                                        <img src="/<?= htmlspecialchars(ltrim($product['image_path'], '/')) ?>" alt="" style="width:64px;height:64px;object-fit:cover;" class="me-3">
                                                    <?php endif; ?>
                                                    <a href="/shop/<?= htmlspecialchars($product['slug']) ?>"><?= htmlspecialchars($product['name']) ?></a>
                                                </div>
                                            </td>
                                            <td class="text-end"><?= htmlspecialchars(Money::format((int) $product['price_cents'])) ?></td>
                                            <td>
                                                <input type="number" class="form-control cart-qty" name="quantities[<?= (int) $product['id'] ?>]" value="<?= (int) $line['quantity'] ?>" min="0" max="<?= min((int) $product['stock'], Cart::MAX_QTY) ?>" aria-label="Quantity for <?= htmlspecialchars($product['name']) ?>">
                                            </td>
                                            <td class="text-end"><?= htmlspecialchars(Money::format($line['line_cents'])) ?></td>
                                            <td class="text-end">
                                                <button type="submit" form="remove-<?= (int) $product['id'] ?>" class="btn btn-sm btn-link text-danger" aria-label="Remove <?= htmlspecialchars($product['name']) ?>"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-outline-secondary cart-update-btn">Update Cart</button>
                        <a href="/shop" class="btn btn-link">Continue Shopping</a>
                    </form>

                    <?php foreach ($cart['lines'] as $line): ?>
                        <form action="/cart/remove" method="post" id="remove-<?= (int) $line['product']['id'] ?>" class="d-none">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="product_id" value="<?= (int) $line['product']['id'] ?>">
                        </form>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-4">
                    <div class="bg-light p-4">
                        <h4 class="mb-4">Order Summary</h4>
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span><?= htmlspecialchars(Money::format($cart['subtotal_cents'])) ?></span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span><?= $shippingCents > 0 ? htmlspecialchars(Money::format($shippingCents)) : 'Free' ?></span></div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-4"><span>Total</span><span><?= htmlspecialchars(Money::format($totalCents)) ?></span></div>

                        <?php if ($paymentsEnabled): ?>
                            <form action="/checkout" method="post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-primary w-100 py-3"><i class="fa fa-lock me-2"></i>Checkout</button>
                            </form>
                            <p class="small text-muted mt-3 mb-0">No account needed. You'll enter your email and shipping address on Stripe's secure payment page, and we'll email your order details.</p>
                        <?php else: ?>
                            <p class="mb-0">Online checkout is temporarily unavailable. Please <a href="/contact">contact us</a> to place an order.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="/js/cart.js" defer></script>
