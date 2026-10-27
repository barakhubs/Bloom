<?php

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Stripe;
use App\Models\Donation;
use App\Models\DonationScheme;

/** Read-only donations ledger. Refunds are issued from the Stripe dashboard. */
class DonationsController extends AdminController
{
    protected ?string $permission = 'donations';

    public function index(): void
    {
        $status = (string) ($_GET['status'] ?? 'paid');
        $status = in_array($status, Donation::STATUSES, true) ? $status : null; // anything else = all
        $schemeParam = (string) ($_GET['scheme'] ?? '');
        $schemeId = $schemeParam === '' ? null : (int) $schemeParam;

        $model = new Donation();

        $this->view('admin/donations/index', [
            'title' => 'Donations - Bloom Beyond Borders Admin',
            'donations' => $model->list($status, $schemeId),
            'totals' => $model->paidTotals(),
            'schemes' => (new DonationScheme())->all(),
            'status' => $status,
            'schemeId' => $schemeId,
            'testMode' => Stripe::isTestMode(),
        ]);
    }
}
