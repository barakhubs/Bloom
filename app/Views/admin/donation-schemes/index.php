<?php
/** @var array $schemes */
/** @var string $csrfToken */

use App\Core\Money;
use App\Models\DonationScheme;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Donation Schemes</h2>
    <div>
        <a href="/admin/donations" class="btn btn-outline-secondary">View Donations</a>
        <a href="/admin/donation-schemes/create" class="btn btn-primary">Add Scheme</a>
    </div>
</div>

<p class="text-muted">Campaigns shown on the public <a href="/donate" target="_blank">Donate</a> page. Visitors can always make a general donation of any amount as well.</p>

<div class="bg-white shadow-sm">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Suggested Amounts</th>
                <th>Raised / Goal</th>
                <th>Status</th>
                <th>Sort</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($schemes)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No donation schemes yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($schemes as $scheme): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($scheme['name']) ?>
                        <div class="small text-muted">/donate/<?= htmlspecialchars($scheme['slug']) ?></div>
                    </td>
                    <td class="small">
                        <?= htmlspecialchars(implode(', ', array_map([Money::class, 'format'], DonationScheme::presets($scheme)))) ?: '&mdash;' ?>
                        <?php if ((int) $scheme['allow_custom_amount'] === 1): ?><div class="text-muted">+ any amount</div><?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(Money::format((int) $scheme['raised_cents'])) ?>
                        <?php if (!empty($scheme['goal_cents'])): ?>
                            <span class="text-muted">/ <?= htmlspecialchars(Money::format((int) $scheme['goal_cents'])) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int) $scheme['is_active'] === 1): ?>
                            <span class="badge bg-primary text-dark">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $scheme['sort_order'] ?></td>
                    <td class="text-end text-nowrap">
                        <a href="/admin/donations?status=&amp;scheme=<?= (int) $scheme['id'] ?>" class="btn btn-sm btn-outline-secondary">Donations</a>
                        <a href="/admin/donation-schemes/<?= (int) $scheme['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="/admin/donation-schemes/<?= (int) $scheme['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this scheme? Past donations are kept but will no longer be linked to it.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
