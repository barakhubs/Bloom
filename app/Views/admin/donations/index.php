<?php
/** @var array $donations */
/** @var array $totals */
/** @var array $schemes */
/** @var string|null $status */
/** @var int|null $schemeId */
/** @var bool $testMode */

use App\Core\Money;
use App\Models\Donation;

$badge = ['paid' => 'bg-primary text-dark', 'pending' => 'bg-warning text-dark', 'expired' => 'bg-light text-dark border', 'failed' => 'bg-danger'];
$stripeBase = 'https://dashboard.stripe.com/' . ($testMode ? 'test/' : '') . 'payments/';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Donations</h2>
    <a href="/admin/donation-schemes" class="btn btn-outline-secondary">Manage Schemes</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="bg-white p-3 shadow-sm text-center">
            <div class="display-6 mb-0"><?= htmlspecialchars(Money::format($totals['cents'])) ?></div>
            <div class="text-muted small">Total received</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bg-white p-3 shadow-sm text-center">
            <div class="display-6 mb-0"><?= (int) $totals['count'] ?></div>
            <div class="text-muted small">Paid donations</div>
        </div>
    </div>
</div>

<form method="get" action="/admin/donations" class="bg-white p-3 shadow-sm mb-3 row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label small mb-1">Status</label>
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            <?php foreach (Donation::STATUSES as $option): ?>
                <option value="<?= $option ?>"<?= $status === $option ? ' selected' : '' ?>><?= ucfirst($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-5">
        <label class="form-label small mb-1">Scheme</label>
        <select name="scheme" class="form-select">
            <option value="">All schemes</option>
            <option value="0"<?= $schemeId === 0 ? ' selected' : '' ?>>General donations</option>
            <?php foreach ($schemes as $scheme): ?>
                <option value="<?= (int) $scheme['id'] ?>"<?= $schemeId === (int) $scheme['id'] ? ' selected' : '' ?>><?= htmlspecialchars($scheme['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-secondary w-100">Filter</button>
    </div>
</form>

<div class="bg-white shadow-sm table-responsive">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Date</th>
                <th>Donor</th>
                <th>Towards</th>
                <th class="text-end">Amount</th>
                <th>Status</th>
                <th>Stripe</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($donations)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No donations match these filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($donations as $donation): ?>
                <tr>
                    <td class="small text-nowrap"><?= htmlspecialchars(date('j M Y, H:i', strtotime((string) ($donation['paid_at'] ?? $donation['created_at'])))) ?></td>
                    <td>
                        <?= htmlspecialchars($donation['donor_name'] ?: '—') ?>
                        <?php if (!empty($donation['donor_email'])): ?>
                            <div class="small"><a href="mailto:<?= htmlspecialchars($donation['donor_email']) ?>"><?= htmlspecialchars($donation['donor_email']) ?></a></div>
                        <?php endif; ?>
                        <?php if (!empty($donation['message'])): ?>
                            <div class="small text-muted fst-italic">&ldquo;<?= htmlspecialchars($donation['message']) ?>&rdquo;</div>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <?php if (!empty($donation['scheme_name'])): ?>
                            <?= htmlspecialchars($donation['scheme_name']) ?>
                        <?php elseif ($donation['scheme_id'] === null): ?>
                            <span class="text-muted">General</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?= htmlspecialchars(Money::format((int) $donation['amount_cents'])) ?></td>
                    <td><span class="badge <?= $badge[$donation['status']] ?? 'bg-secondary' ?>"><?= htmlspecialchars(ucfirst($donation['status'])) ?></span></td>
                    <td class="small">
                        <?php if (!empty($donation['stripe_payment_intent_id'])): ?>
                            <a href="<?= htmlspecialchars($stripeBase . $donation['stripe_payment_intent_id']) ?>" target="_blank" rel="noopener">View</a>
                        <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="small text-muted mt-2">Showing the latest 500. Refunds are issued from the Stripe dashboard.</p>
