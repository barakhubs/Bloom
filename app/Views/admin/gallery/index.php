<?php
/** @var array $albums */
/** @var string $csrfToken */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Gallery</h2>
    <a href="/admin/gallery/create" class="btn btn-primary">Add Album</a>
</div>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Images</th>
                <th>Sort</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($albums)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No albums yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($albums as $album): ?>
                <tr>
                    <td><a href="/admin/gallery/<?= (int) $album['id'] ?>"><?= htmlspecialchars($album['name']) ?></a></td>
                    <td class="text-muted small"><?= htmlspecialchars($album['slug']) ?></td>
                    <td><?= (int) $album['image_count'] ?></td>
                    <td><?= (int) $album['sort_order'] ?></td>
                    <td class="text-end">
                        <a href="/admin/gallery/<?= (int) $album['id'] ?>" class="btn btn-sm btn-outline-secondary">Manage Images</a>
                        <a href="/admin/gallery/<?= (int) $album['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="/admin/gallery/<?= (int) $album['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this album and all its images? This cannot be undone.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
