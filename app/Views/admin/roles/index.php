<?php
/** @var array $roles */
/** @var array|null $flash */
/** @var string $csrfToken */

use App\Core\Permissions;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Roles</h2>
    <div>
        <a href="/admin/users" class="btn btn-outline-secondary me-2">Back to Users</a>
        <a href="/admin/roles/create" class="btn btn-primary">Add Role</a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Role</th>
                <th>Can manage</th>
                <th>Users</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($role['name']) ?></td>
                    <td class="small">
                        <?php if (!empty($role['is_system'])): ?>
                            <span class="text-muted">Everything (built-in)</span>
                        <?php elseif (empty($role['permissions'])): ?>
                            <span class="text-muted">Nothing yet</span>
                        <?php else: ?>
                            <?= htmlspecialchars(implode(', ', array_map(
                                fn ($slug) => Permissions::ALL[$slug] ?? $slug,
                                array_values(array_intersect(array_keys(Permissions::ALL), $role['permissions']))
                            ))) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $role['user_count'] ?></td>
                    <td class="text-end text-nowrap">
                        <?php if (empty($role['is_system'])): ?>
                            <a href="/admin/roles/<?= (int) $role['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="/admin/roles/<?= (int) $role['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this role?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted small">Can't be changed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
