<?php
/** @var string|null $email */
require dirname(__DIR__, 2) . '/partials/page-header.php';
?>

<div class="container-fluid pb-5">
    <div class="container text-center" style="max-width: 640px;">
        <i class="fa fa-envelope-open-text fa-4x text-primary mb-4"></i>
        <h2 class="display-6 mb-4">Check Your Inbox</h2>
        <p class="fs-5">
            We've sent an email to <strong><?= htmlspecialchars($email ?: 'your address') ?></strong>.
            Follow the link inside to confirm your address. Once it's confirmed, any earlier orders and donations made with this email will appear in your account.
        </p>
        <p class="mb-4">You can sign in now. Didn't get it? Check your spam folder, or sign in to send a new link.</p>
        <a href="/account/login" class="btn btn-primary py-3 px-4">Sign In</a>
    </div>
</div>
