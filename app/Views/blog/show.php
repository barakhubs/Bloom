<?php
/** @var array $post */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (!empty($post['featured_image_path'])): ?>
                    <img class="img-fluid w-100 mb-4" src="/<?= htmlspecialchars(ltrim($post['featured_image_path'], '/')) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                <?php endif; ?>
                <span class="text-primary small text-uppercase"><?= htmlspecialchars(date('F j, Y', strtotime((string) $post['published_at']))) ?></span>
                <h1 class="display-6 mb-4"><?= htmlspecialchars($post['title']) ?></h1>
                <p class="fs-5"><?= nl2br(htmlspecialchars((string) $post['body'])) ?></p>

                <a href="/blog" class="btn btn-secondary py-2 px-4 mt-4"><i class="fa fa-arrow-left me-2"></i>Back to Blog</a>
            </div>
        </div>
    </div>
</div>
