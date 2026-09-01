<?php
/** @var string $pageTitle */
$pageTitle = $pageTitle ?? '';
?>
<!-- Page Header Start -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container py-5">
        <h1 class="display-3 text-white text-uppercase"><?= htmlspecialchars($pageTitle) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($pageTitle) ?></li>
            </ol>
        </nav>
    </div>
</div>
<!-- Page Header End -->
