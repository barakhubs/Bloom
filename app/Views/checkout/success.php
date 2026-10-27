<?php
/** @var array $order */
/** @var bool $confirmed */
/** @var string|null $email */

use App\Core\Money;

require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid pb-5">
    <div class="container" style="max-width: 760px;">
        <div class="text-center mb-5">
            <?php if ($confirmed): ?>
                <i class="fa fa-circle-check fa-4x text-primary mb-4"></i>
                <h2 class="display-6 mb-3">Thank You for Your Order</h2>
                <p class="fs-5 mb-1">Your order reference is <strong><?= htmlspecialchars($order['reference']) ?></strong>.</p>
                <p class="mb-0">A confirmation<?= $email ? ' has been emailed to <strong>' . htmlspecialchars($email) . '</strong>' : ' is on its way to your inbox' ?>. We'll email you again when it ships.</p>
            <?php elseif ($order['status'] === 'pending'): ?>
                <i class="fa fa-hourglass-half fa-4x text-primary mb-4"></i>
                <h2 class="display-6 mb-3">We're Confirming Your Payment</h2>
                <p class="mb-0">Order <strong><?= htmlspecialchars($order['reference']) ?></strong>. This usually takes a few seconds; your confirmation email will follow.</p>
            <?php else: ?>
                <i class="fa fa-circle-exclamation fa-4x text-primary mb-4"></i>
                <h2 class="display-6 mb-3">This Order Wasn't Completed</h2>
                <p class="mb-0">No payment was taken. <a href="/cart">Return to your cart</a> to try again.</p>
            <?php endif; ?>
        </div>

        <?php if ($confirmed || $order['status'] === 'pending'): ?>
            <div class="bg-light p-4">
                <?php foreach ($order['items'] as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= (int) $item['quantity'] ?> &times; <?= htmlspecialchars($item['product_name']) ?></span>
                        <span><?= htmlspecialchars(Money::format((int) $item['unit_price_cents'] * (int) $item['quantity'])) ?></span>
                    </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between mb-1"><span>Shipping</span><span><?= htmlspecialchars(Money::format((int) $order['shipping_cents'])) ?></span></div>
                <div class="d-flex justify-content-between fw-bold"><span>Total</span><span><?= htmlspecialchars(Money::format((int) $order['total_cents'])) ?></span></div>
            </div>
        <?php endif; ?>

        <?php if ($confirmed && !\App\Core\CustomerAuth::check()): ?>
            <div class="border p-4 mt-4 text-center">
                <p class="mb-2"><strong>Want to track this order?</strong> Create a free account with the same email. Once you confirm it, this order will appear in your account.</p>
                <a href="/account/register<?= $email ? '?email=' . htmlspecialchars(rawurlencode($email)) : '' ?>" class="btn btn-outline-secondary">Create an Account</a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="/shop" class="btn btn-primary py-3 px-4 me-2">Continue Shopping</a>
            <a href="/" class="btn btn-secondary py-3 px-4">Back to Home</a>
        </div>
    </div>
</div>
