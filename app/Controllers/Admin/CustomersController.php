<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\CustomerLinks;
use App\Models\Customer;
use App\Models\Donation;
use App\Models\Order;

/** Optional public customer accounts. Admins can look, send a reset link, or delete - never set a password. */
class CustomersController extends AdminController
{
    protected ?string $permission = 'customers';

    public function index(): void
    {
        $search = trim((string) ($_GET['q'] ?? ''));

        $this->view('admin/customers/index', [
            'title' => 'Customers - Bloom Beyond Borders Admin',
            'customers' => (new Customer())->list($search),
            'search' => $search,
        ]);
    }

    public function show(string $id): void
    {
        $customer = (new Customer())->find((int) $id);

        if ($customer === null) {
            (new ErrorController())->notFound();

            return;
        }

        $flash = $_SESSION['customer_flash'] ?? null;
        unset($_SESSION['customer_flash']);

        $this->view('admin/customers/show', [
            'title' => $customer['name'] . ' - Customers - Bloom Beyond Borders Admin',
            'customer' => $customer,
            'orders' => (new Order())->listForCustomer((int) $customer['id']),
            'donations' => (new Donation())->listForCustomer((int) $customer['id']),
            'flash' => $flash,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function sendReset(string $id): void
    {
        $customer = (new Customer())->find((int) $id);

        if ($customer === null || !Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/customers');
            exit;
        }

        $_SESSION['customer_flash'] = CustomerLinks::send($customer, 'reset')
            ? 'A password-reset link has been emailed to ' . $customer['email'] . '.'
            : 'The reset email could not be sent. Check the SMTP settings.';
        header('Location: /admin/customers/' . (int) $customer['id']);
        exit;
    }

    /** Orders and donations are kept (unlinked) for the records. */
    public function destroy(string $id): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            (new Customer())->delete((int) $id);
        }

        header('Location: /admin/customers');
        exit;
    }
}
