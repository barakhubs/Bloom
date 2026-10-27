<?php
/** @var array $customers */
/** @var string $search */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Customers</h2>
</div>

<p class="text-muted">Optional accounts created by shoppers and donors. Guests who never sign up don't appear here; their orders and donations are still listed under Orders and Donations.</p>

<form method="get" action="/admin/customers" class="bg-white p-3 shadow-sm mb-3 d-flex gap-2">
    <input type="search" name="q" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-secondary">Search</button>
</form>

<div class="bg-white shadow-sm table-responsive">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th class="text-end">Orders</th>
                <th class="text-end">Donations</th>
                <th>Joined</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No customer accounts<?= $search !== '' ? ' match that search' : ' yet' ?>.</td></tr>
            <?php endif; ?>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?= htmlspecialchars($customer['name']) ?></td>
                    <td>
                        <?= htmlspecialchars($customer['email']) ?>
                        <?php if ($customer['email_verified_at'] === null): ?><span class="badge bg-warning text-dark ms-1">Unconfirmed</span><?php endif; ?>
                    </td>
                    <td class="text-end"><?= (int) $customer['order_count'] ?></td>
                    <td class="text-end"><?= (int) $customer['donation_count'] ?></td>
                    <td class="small"><?= htmlspecialchars(date('j M Y', strtotime((string) $customer['created_at']))) ?></td>
                    <td class="text-end"><a href="/admin/customers/<?= (int) $customer['id'] ?>" class="btn btn-sm btn-secondary">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
