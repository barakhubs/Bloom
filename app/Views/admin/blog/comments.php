<?php
/** @var array $comments */
/** @var string $csrfToken */
?>
<h2 class="mb-4">Comments</h2>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Post</th>
                <th>Author</th>
                <th>Comment</th>
                <th>Status</th>
                <th>Date</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($comments)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No comments yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><a href="/blog/<?= htmlspecialchars($comment['post_slug']) ?>" target="_blank"><?= htmlspecialchars($comment['post_title']) ?></a></td>
                    <td>
                        <?= htmlspecialchars($comment['author_name']) ?>
                        <span class="text-muted small d-block"><?= htmlspecialchars($comment['author_email']) ?></span>
                    </td>
                    <td style="max-width: 320px;"><?= htmlspecialchars(mb_strimwidth((string) $comment['body'], 0, 140, '...')) ?></td>
                    <td>
                        <?php if ($comment['status'] === 'approved'): ?>
                            <span class="badge bg-primary text-dark">Approved</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars(date('M j, Y', strtotime((string) $comment['created_at']))) ?></td>
                    <td class="text-end">
                        <?php if ($comment['status'] !== 'approved'): ?>
                            <form action="/admin/comments/<?= (int) $comment['id'] ?>/approve" method="post" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                            </form>
                        <?php endif; ?>
                        <form action="/admin/comments/<?= (int) $comment['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this comment?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
