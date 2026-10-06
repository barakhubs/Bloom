<?php
/** @var array|null $currentAdmin */
/** @var array $tiles */
?>
<h2 class="mb-1">Dashboard</h2>
<p class="text-muted mb-4">
    Logged in as <?= htmlspecialchars($currentAdmin['name'] ?: $currentAdmin['email']) ?>
    (<?= htmlspecialchars($currentAdmin['role_name']) ?>).
</p>

<?php if (empty($tiles)): ?>
    <div class="bg-white p-4 shadow-sm text-muted">
        Your role doesn't have access to any content sections yet. Ask an administrator to grant you permissions.
    </div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <?php foreach ($tiles as $tile): ?>
            <div class="col-6 col-md-3">
                <a href="<?= htmlspecialchars($tile['href']) ?>" class="d-block text-decoration-none text-dark bg-white p-3 shadow-sm text-center<?= $tile['highlight'] ? ' border border-primary border-2' : '' ?>">
                    <div class="display-6 mb-0"><?= (int) $tile['value'] ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($tile['label']) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
