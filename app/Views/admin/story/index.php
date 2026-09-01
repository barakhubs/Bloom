<?php
/** @var array $stats */
/** @var array $financeEntries */
/** @var array|null $boardLetter */
/** @var array $errors */
/** @var bool $success */
/** @var string $csrfToken */
?>
<h2 class="mb-4">Our Story Content</h2>

<?php if ($success): ?>
    <div class="alert alert-success" role="alert">Saved.</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Impact Stats</h5>
    <a href="/admin/story/stats/create" class="btn btn-sm btn-primary">Add Stat</a>
</div>
<div class="bg-white shadow-sm mb-4">
    <table class="table mb-0 align-middle">
        <thead><tr><th>Label</th><th>Value</th><th>Sort</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            <?php if (empty($stats)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">No stats yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($stats as $stat): ?>
                <tr>
                    <td><?= htmlspecialchars($stat['label']) ?></td>
                    <td><?= htmlspecialchars($stat['value']) ?></td>
                    <td><?= (int) $stat['sort_order'] ?></td>
                    <td class="text-end">
                        <a href="/admin/story/stats/<?= (int) $stat['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="/admin/story/stats/<?= (int) $stat['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this stat?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Financial Allocation</h5>
    <a href="/admin/story/finance/create" class="btn btn-sm btn-primary">Add Entry</a>
</div>
<div class="bg-white shadow-sm mb-4">
    <table class="table mb-0 align-middle">
        <thead><tr><th>Year</th><th>Category</th><th>Percentage</th><th>Sort</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            <?php if (empty($financeEntries)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No entries yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($financeEntries as $entry): ?>
                <tr>
                    <td><?= (int) $entry['year'] ?></td>
                    <td><?= htmlspecialchars($entry['category']) ?></td>
                    <td><?= htmlspecialchars(rtrim(rtrim(number_format((float) $entry['percentage'], 2), '0'), '.')) ?>%</td>
                    <td><?= (int) $entry['sort_order'] ?></td>
                    <td class="text-end">
                        <a href="/admin/story/finance/<?= (int) $entry['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="/admin/story/finance/<?= (int) $entry['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Delete this entry?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h5 class="mb-3">Board Letter</h5>
<?php if (!empty($errors['letter'])): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errors['letter']) ?></div>
<?php endif; ?>
<form action="/admin/story/letter" method="post" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Author Name</label>
            <input type="text" class="form-control" name="author_name" value="<?= htmlspecialchars($boardLetter['author_name'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Author Title</label>
            <input type="text" class="form-control" name="author_title" value="<?= htmlspecialchars($boardLetter['author_title'] ?? '') ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Letter</label>
            <textarea class="form-control" name="body" style="height: 150px;"><?= htmlspecialchars($boardLetter['body'] ?? '') ?></textarea>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary px-4">Save Letter</button>
    </div>
</form>
