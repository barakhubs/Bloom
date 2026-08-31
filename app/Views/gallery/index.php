<?php
/** @var array $albums */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container">
        <?php
        $hasAnyImages = false;
        foreach ($albums as $album) {
            if (!empty($album['images'])) {
                $hasAnyImages = true;
                break;
            }
        }
        ?>

        <?php if (!$hasAnyImages): ?>
            <p class="text-center text-muted">Photos will appear here once albums are added via the back office.</p>
        <?php endif; ?>

        <?php foreach ($albums as $album): ?>
            <?php if (empty($album['images'])) continue; ?>
            <div class="mb-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="text-center mx-auto mb-4" style="max-width: 500px;">
                    <p class="section-title bg-white text-center text-primary px-3"><?= htmlspecialchars($album['name']) ?></p>
                </div>
                <div class="row g-3">
                    <?php foreach ($album['images'] as $image): ?>
                        <?php $path = '/' . htmlspecialchars(ltrim($image['image_path'], '/')); ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <img
                                class="img-fluid w-100 gallery-thumb"
                                style="cursor: pointer; aspect-ratio: 4 / 3; object-fit: cover;"
                                src="<?= $path ?>"
                                data-full="<?= $path ?>"
                                data-caption="<?= htmlspecialchars($image['caption'] ?? '') ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#galleryLightbox"
                                alt="<?= htmlspecialchars($image['caption'] ?: $album['name']) ?>"
                            >
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<!-- Gallery Lightbox Start -->
<div class="modal fade" id="galleryLightbox" tabindex="-1" aria-labelledby="galleryLightboxLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-0">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="galleryLightboxLabel">Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0 text-center">
                <img id="galleryLightboxImage" class="img-fluid" src="" alt="">
                <p id="galleryLightboxCaption" class="mt-3 mb-0"></p>
            </div>
        </div>
    </div>
</div>
<!-- Gallery Lightbox End -->
