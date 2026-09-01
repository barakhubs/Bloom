<?php
/** @var array $posts */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container">
        <?php if (!empty($posts)): ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-lg-3 wow fadeIn" data-wow-delay="0.1s">
                        <div class="donation-item h-100 p-4">
                            <?php if (!empty($post['featured_image_path'])): ?>
                                <div class="mb-4">
                                    <img class="img-fluid w-100" style="aspect-ratio: 4 / 3; object-fit: cover;" src="/<?= htmlspecialchars(ltrim($post['featured_image_path'], '/')) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                                </div>
                            <?php endif; ?>
                            <span class="text-primary small text-uppercase"><?= htmlspecialchars(date('M j, Y', strtotime((string) $post['published_at']))) ?></span>
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="h4 d-block mt-1 mb-2"><?= htmlspecialchars($post['title']) ?></a>
                            <p class="mb-3"><?= htmlspecialchars(mb_strimwidth(strip_tags((string) $post['body']), 0, 110, '...')) ?></p>
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="btn btn-primary w-100 py-2">Read More</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Blog posts will appear here once published via the back office.</p>
        <?php endif; ?>
    </div>
</div>
