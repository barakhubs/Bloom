<?php
/** @var array $posts */
/** @var string $csrfToken */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Blog</h2>
    <a href="/admin/blog/create" class="btn btn-primary">Add Post</a>
</div>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Published</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($posts)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No posts yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= htmlspecialchars($post['title']) ?></td>
                    <td>
                        <?php if ($post['status'] === 'published'): ?>
                            <span class="badge bg-primary text-dark">Published</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= $post['published_at'] ? htmlspecialchars(date('M j, Y', strtotime((string) $post['published_at']))) : '&mdash;' ?></td>
                    <td class="text-end">
                        <?php if ($post['status'] === 'published'): ?>
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">View</a>
                        <?php endif; ?>
                        <a href="/admin/blog/<?= (int) $post['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="/admin/blog/<?= (int) $post['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this post and all its comments?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
