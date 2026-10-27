<?php
/** @var array $donations */
/** @var string $csrfToken */

use App\Core\Money;

require __DIR__ . '/_start.php';

$totalCents = array_sum(array_map(fn (array $donation) => (int) $donation['amount_cents'], $donations));
?>
<h2 class="h3 mb-4">My Donations</h2>

<?php if (empty($donations)): ?>
    <div class="bg-light p-4">
        <p class="mb-3">You haven't made any donations yet.</p>
        <a href="/donate" class="btn btn-primary">Make a Donation</a>
    </div>
<?php else: ?>
    <p class="mb-4">Thank you! You've given <strong><?= htmlspecialchars(Money::format($totalCents)) ?></strong> in total.</p>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Date</th><th>Towards</th><th class="text-end">Amount</th><th>Reference</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($donations as $donation): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('j M Y', strtotime((string) $donation['paid_at']))) ?></td>
                        <td><?= htmlspecialchars($donation['scheme_name'] ?? 'General donation') ?></td>
                        <td class="text-end"><?= htmlspecialchars(Money::format((int) $donation['amount_cents'])) ?></td>
                        <td class="small text-muted">D-<?= (int) $donation['id'] ?></td>
                        <td class="text-end">
                            <?php if (!empty($donation['donor_email'])): ?>
                                <form action="/account/donations/<?= (int) $donation['id'] ?>/resend" method="post" class="m-0">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Email Receipt</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/_end.php'; ?>
