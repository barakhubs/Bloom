<?php
/** @var array $submissions */
/** @var string $csrfToken */
?>
<h2 class="mb-4">Contact Submissions</h2>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Received</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($submissions)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No submissions yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($submissions as $submission): ?>
                <tr>
                    <td><?= htmlspecialchars($submission['name']) ?></td>
                    <td><a href="mailto:<?= htmlspecialchars($submission['email']) ?>"><?= htmlspecialchars($submission['email']) ?></a></td>
                    <td style="max-width: 380px;"><?= htmlspecialchars($submission['message']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars(date('M j, Y g:ia', strtotime((string) $submission['created_at']))) ?></td>
                    <td class="text-end">
                        <form action="/admin/contact/<?= (int) $submission['id'] ?>/delete" method="post" onsubmit="return confirm('Delete this submission?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
