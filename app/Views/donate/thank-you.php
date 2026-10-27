<?php
/** @var array $donation */
/** @var bool $confirmed */

use App\Core\Money;

require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container text-center" style="max-width: 700px;">
        <?php if ($confirmed): ?>
            <i class="fa fa-heart fa-4x text-primary mb-4"></i>
            <h2 class="display-6 mb-4">Thank You for Your Generosity</h2>
            <p class="fs-5 mb-4">
                Your donation of <strong><?= htmlspecialchars(Money::format((int) $donation['amount_cents'])) ?></strong>
                <?php if (!empty($donation['scheme_name'])): ?>towards <strong><?= htmlspecialchars($donation['scheme_name']) ?></strong><?php endif; ?>
                has been received. A receipt is on its way to your inbox.
            </p>
        <?php elseif ($donation['status'] === 'pending'): ?>
            <i class="fa fa-hourglass-half fa-4x text-primary mb-4"></i>
            <h2 class="display-6 mb-4">We're Confirming Your Payment</h2>
            <p class="fs-5 mb-4">This usually takes a few seconds. You'll receive an email receipt as soon as it's confirmed. Thank you for your support!</p>
        <?php else: ?>
            <i class="fa fa-circle-exclamation fa-4x text-primary mb-4"></i>
            <h2 class="display-6 mb-4">This Payment Wasn't Completed</h2>
            <p class="fs-5 mb-4">No money was taken. You're welcome to try again.</p>
        <?php endif; ?>
        <a href="/" class="btn btn-primary py-3 px-4 me-2">Back to Home</a>
        <a href="/donate" class="btn btn-secondary py-3 px-4">Donate Again</a>
    </div>
</div>
