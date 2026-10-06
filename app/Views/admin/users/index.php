<?php
/** @var array $users */
/** @var array|null $flash */
/** @var string $csrfToken */

use App\Core\Auth;

$statusBadges = [
    'active' => 'bg-success',
    'invited' => 'bg-warning text-dark',
    'disabled' => 'bg-secondary',
];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Users</h2>
    <div>
        <a href="/admin/roles" class="btn btn-outline-secondary me-2">Manage Roles</a>
        <a href="/admin/users/create" class="btn btn-primary">Invite User</a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <?php if (!empty($flash['link'])): ?>
            <input type="text" class="form-control form-control-sm mt-2" value="<?= htmlspecialchars($flash['link']) ?>" readonly onclick="this.select();">
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <?php $isSelf = (int) $user['id'] === Auth::id(); ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?><?= $isSelf ? ' <span class="text-muted small">(you)</span>' : '' ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role_name']) ?></td>
                    <td><span class="badge <?= $statusBadges[$user['status']] ?? 'bg-secondary' ?>"><?= htmlspecialchars(ucfirst($user['status'])) ?></span></td>
                    <td class="small text-muted"><?= $user['last_login_at'] ? htmlspecialchars(date('M j, Y g:ia', strtotime($user['last_login_at']))) : '&mdash;' ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($user['status'] !== 'disabled'): ?>
                            <form action="/admin/users/<?= (int) $user['id'] ?>/send-link" method="post" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $user['status'] === 'invited' ? 'Resend Invite' : 'Send Reset Link' ?></button>
                            </form>
                        <?php endif; ?>
                        <a href="/admin/users/<?= (int) $user['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <?php if (!$isSelf): ?>
                            <form action="/admin/users/<?= (int) $user['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this user? They will lose access immediately.');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
