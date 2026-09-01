<?php
/** @var array|null $currentAdmin */
/** @var array $counts */

$tiles = [
    ['label' => 'Pending Comments', 'value' => $counts['pending_comments'], 'highlight' => $counts['pending_comments'] > 0],
    ['label' => 'Contact Submissions', 'value' => $counts['contact_submissions']],
    ['label' => 'Team Members', 'value' => $counts['team_members']],
    ['label' => 'Partners', 'value' => $counts['partners']],
    ['label' => 'Gallery Albums', 'value' => $counts['gallery_albums']],
    ['label' => 'Gallery Images', 'value' => $counts['gallery_images']],
    ['label' => 'Published Posts', 'value' => $counts['published_posts']],
];
?>
<h2 class="mb-1">Dashboard</h2>
<p class="text-muted mb-4">Logged in as <?= htmlspecialchars($currentAdmin['email'] ?? '') ?>.</p>

<div class="row g-3 mb-4">
    <?php foreach ($tiles as $tile): ?>
        <div class="col-6 col-md-3">
            <div class="bg-white p-3 shadow-sm text-center<?= !empty($tile['highlight']) ? ' border border-primary border-2' : '' ?>">
                <div class="display-6 mb-0"><?= (int) $tile['value'] ?></div>
                <div class="text-muted small"><?= htmlspecialchars($tile['label']) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-white p-4 shadow-sm">
    <h5 class="mb-3">Manage</h5>
    <a href="/admin/settings" class="btn btn-primary me-2 mb-2">Site Settings</a>
    <span class="text-muted small d-block mt-2">Team, Partners, Gallery, Blog, Contact submissions, and Our Story content management land here across the remaining <code>feature/admin-*</code> branches.</span>
</div>
