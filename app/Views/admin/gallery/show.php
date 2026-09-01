<?php
/** @var array $album */
/** @var array $images */
/** @var array $errors */
/** @var string $csrfToken */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/gallery" class="text-muted small d-block mb-1"><i class="fa fa-arrow-left me-1"></i>Back to Albums</a>
        <h2 class="mb-0"><?= htmlspecialchars($album['name']) ?></h2>
    </div>
</div>

<div class="bg-white p-4 shadow-sm mb-4">
    <h5 class="mb-3">Add Image</h5>
    <?php if (!empty($errors['image'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errors['image']) ?></div>
    <?php endif; ?>
    <form action="/admin/gallery/<?= (int) $album['id'] ?>/images" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Image</label>
                <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <div class="col-md-4">
                <label class="form-label">Caption (optional)</label>
                <input type="text" class="form-control" name="caption">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" name="sort_order" value="0">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Upload</button>
            </div>
        </div>
    </form>
</div>

<div class="row g-3">
    <?php if (empty($images)): ?>
        <p class="text-muted">No images in this album yet.</p>
    <?php endif; ?>
    <?php foreach ($images as $image): ?>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="bg-white shadow-sm p-2">
                <img src="/<?= htmlspecialchars(ltrim($image['image_path'], '/')) ?>" alt="" class="img-fluid w-100 mb-2" style="aspect-ratio: 4 / 3; object-fit: cover;">
                <?php if (!empty($image['caption'])): ?>
                    <p class="small text-muted mb-2"><?= htmlspecialchars($image['caption']) ?></p>
                <?php endif; ?>
                <div class="d-flex justify-content-between">
                    <a href="/admin/gallery/<?= (int) $album['id'] ?>/images/<?= (int) $image['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                    <form action="/admin/gallery/<?= (int) $album['id'] ?>/images/<?= (int) $image['id'] ?>/delete" method="post" onsubmit="return confirm('Delete this image?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
