<?php
/** @var array $partners */
/** @var string $csrfToken */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Partners</h2>
    <a href="/admin/partners/create" class="btn btn-primary">Add Partner</a>
</div>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Logo</th>
                <th>Name</th>
                <th>Link</th>
                <th>Sort</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($partners)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No partners yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($partners as $partner): ?>
                <tr>
                    <td><img src="/<?= htmlspecialchars(ltrim($partner['logo_path'], '/')) ?>" alt="" style="max-height:40px;max-width:80px;"></td>
                    <td><?= htmlspecialchars($partner['name']) ?></td>
                    <td>
                        <?php if (!empty($partner['link_url'])): ?>
                            <a href="<?= htmlspecialchars($partner['link_url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($partner['link_url']) ?></a>
                        <?php else: ?>
                            <span class="text-muted small">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $partner['sort_order'] ?></td>
                    <td class="text-end">
                        <a href="/admin/partners/<?= (int) $partner['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="/admin/partners/<?= (int) $partner['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this partner?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
