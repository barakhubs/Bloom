<?php
/** @var array|null $member */
/** @var array $errors */
/** @var string $csrfToken */
$isEdit = $member !== null;
$actionUrl = $isEdit ? '/admin/team/' . (int) $member['id'] : '/admin/team';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Team Member' : 'Add Team Member' ?></h2>

<form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($member['name'] ?? '') ?>">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" class="form-control<?= isset($errors['title']) ? ' is-invalid' : '' ?>" name="title" value="<?= htmlspecialchars($member['title'] ?? '') ?>">
            <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div><?php endif; ?>
        </div>
        <div class="col-12">
            <label class="form-label">Bio</label>
            <textarea class="form-control" name="bio" style="height: 150px;"><?= htmlspecialchars($member['bio'] ?? '') ?></textarea>
        </div>
        <div class="col-md-8">
            <label class="form-label d-block">Photo</label>
            <?php if (!empty($member['photo_path'])): ?>
                <img src="/<?= htmlspecialchars(ltrim($member['photo_path'], '/')) ?>" alt="" style="width:64px;height:64px;object-fit:cover;" class="rounded-circle mb-2">
            <?php endif; ?>
            <input type="file" class="form-control<?= isset($errors['photo']) ? ' is-invalid' : '' ?>" name="photo" accept=".jpg,.jpeg,.png,.webp">
            <?php if (isset($errors['photo'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['photo']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= (int) ($member['sort_order'] ?? 0) ?>">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Team Member' ?></button>
        <a href="/admin/team" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
