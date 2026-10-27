<?php

namespace App\Controllers\Admin;

use App\Controllers\ErrorController;
use App\Core\AdminController;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\Stripe;
use App\Models\Order;
use App\Models\Product;

/** Order fulfilment. Payment status is set by the Stripe webhook; refunds happen in the Stripe dashboard. */
class OrdersController extends AdminController
{
    protected ?string $permission = 'orders';

    public function index(): void
    {
        $status = (string) ($_GET['status'] ?? 'paid');
        $status = in_array($status, Order::STATUSES, true) ? $status : null; // anything else = all

        $this->view('admin/orders/index', [
            'title' => 'Orders - Bloom Beyond Borders Admin',
            'orders' => (new Order())->list($status),
            'status' => $status,
        ]);
    }

    public function show(string $id): void
    {
        $order = (new Order())->find((int) $id);

        if ($order === null) {
            (new ErrorController())->notFound();

            return;
        }

        $flash = $_SESSION['order_flash'] ?? null;
        unset($_SESSION['order_flash']);

        $this->view('admin/orders/show', [
            'title' => 'Order ' . $order['reference'] . ' - Bloom Beyond Borders Admin',
            'order' => $order,
            'flash' => $flash,
            'testMode' => Stripe::isTestMode(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function ship(string $id): void
    {
        $orderId = (int) $id;

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->back($orderId, 'Your session expired. Please try again.');
        }

        $orders = new Order();

        if (!$orders->markShipped($orderId)) {
            $this->back($orderId, 'Only paid orders can be marked as shipped.');
        }

        $sent = Mailer::sendOrderShipped($orders->find($orderId));
        $this->back($orderId, $sent ? 'Marked as shipped, and the customer has been emailed.' : 'Marked as shipped. (The shipping email could not be sent; check the SMTP settings.)');
    }

    /** For orders refunded in Stripe: closes the order and puts the items back in stock. */
    public function cancel(string $id): void
    {
        $orderId = (int) $id;

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->back($orderId, 'Your session expired. Please try again.');
        }

        $orders = new Order();

        if (!$orders->markCancelled($orderId)) {
            $this->back($orderId, 'Only paid or shipped orders can be cancelled.');
        }

        if (!empty($_POST['restock'])) {
            $products = new Product();
            foreach ($orders->find($orderId)['items'] as $item) {
                if ($item['product_id'] !== null) {
                    $products->incrementStock((int) $item['product_id'], (int) $item['quantity']);
                }
            }
        }

        $this->back($orderId, 'Order cancelled. Remember to issue the refund in the Stripe dashboard if you haven\'t already.');
    }

    private function back(int $orderId, string $message): never
    {
        $_SESSION['order_flash'] = $message;
        header('Location: /admin/orders/' . $orderId);
        exit;
    }
}
