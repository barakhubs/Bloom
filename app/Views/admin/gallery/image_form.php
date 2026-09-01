<?php
/** @var int $albumId */
/** @var array $image */
/** @var string $csrfToken */
?>
<h2 class="mb-4">Edit Image</h2>

<form action="/admin/gallery/<?= $albumId ?>/images/<?= (int) $image['id'] ?>" method="post" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <img src="/<?= htmlspecialchars(ltrim($image['image_path'], '/')) ?>" alt="" class="img-fluid mb-3" style="max-height: 200px;">

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Caption</label>
            <input type="text" class="form-control" name="caption" value="<?= htmlspecialchars($image['caption'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= (int) $image['sort_order'] ?>">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
        <a href="/admin/gallery/<?= $albumId ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
