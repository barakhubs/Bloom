<?php
/** @var array|null $post */
/** @var array $errors */
/** @var string $csrfToken */
$isEdit = $post !== null;
$actionUrl = $isEdit ? '/admin/blog/' . (int) $post['id'] : '/admin/blog';
$currentStatus = $post['status'] ?? 'draft';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Post' : 'Add Post' ?></h2>

<form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input type="text" class="form-control<?= isset($errors['title']) ? ' is-invalid' : '' ?>" name="title" value="<?= htmlspecialchars($post['title'] ?? '') ?>">
            <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $currentStatus === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Body</label>
            <textarea class="form-control<?= isset($errors['body']) ? ' is-invalid' : '' ?>" name="body" style="height: 250px;"><?= htmlspecialchars($post['body'] ?? '') ?></textarea>
            <?php if (isset($errors['body'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['body']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-8">
            <label class="form-label d-block">Featured Image</label>
            <?php if (!empty($post['featured_image_path'])): ?>
                <img src="/<?= htmlspecialchars(ltrim($post['featured_image_path'], '/')) ?>" alt="" style="max-height: 100px;" class="mb-2 d-block">
            <?php endif; ?>
            <input type="file" class="form-control<?= isset($errors['featured_image']) ? ' is-invalid' : '' ?>" name="featured_image" accept=".jpg,.jpeg,.png,.webp">
            <?php if (isset($errors['featured_image'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['featured_image']) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Post' ?></button>
        <a href="/admin/blog" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
