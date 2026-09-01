<?php
/** @var array $members */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Team</h2>
    <a href="/admin/team/create" class="btn btn-primary">Add Team Member</a>
</div>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Title</th>
                <th>Sort</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($members)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No team members yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td>
                        <?php if (!empty($member['photo_path'])): ?>
                            <img src="/<?= htmlspecialchars(ltrim($member['photo_path'], '/')) ?>" alt="" style="width:48px;height:48px;object-fit:cover;" class="rounded-circle">
                        <?php else: ?>
                            <span class="text-muted small">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($member['name']) ?></td>
                    <td><?= htmlspecialchars($member['title']) ?></td>
                    <td><?= (int) $member['sort_order'] ?></td>
                    <td class="text-end">
                        <a href="/admin/team/<?= (int) $member['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="/admin/team/<?= (int) $member['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this team member?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
