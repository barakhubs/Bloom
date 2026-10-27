<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Csrf;
use App\Core\CustomerController;
use App\Core\CustomerLinks;
use App\Core\Mailer;
use App\Core\Shop;
use App\Models\Donation;
use App\Models\Order;
use App\Models\Product;
use App\Models\WishlistItem;

/** Signed-in customer area: dashboard, order + donation history, wishlist. */
class AccountController extends CustomerController
{
    public function index(): void
    {
        $customerId = (int) $this->customer['id'];

        $this->render('account/index', 'My Account', [
            'orders' => array_slice((new Order())->listForCustomer($customerId), 0, 3),
            'donations' => array_slice((new Donation())->listForCustomer($customerId), 0, 3),
            'wishlistCount' => Shop::enabled() ? count((new WishlistItem())->productIds($customerId)) : null,
        ]);
    }

    public function orders(): void
    {
        $this->render('account/orders', 'My Orders', [
            'orders' => (new Order())->listForCustomer((int) $this->customer['id']),
        ]);
    }

    public function order(string $reference): void
    {
        $order = (new Order())->findForCustomer($reference, (int) $this->customer['id']);

        if ($order === null) {
            (new ErrorController())->notFound();

            return;
        }

        $this->render('account/order', 'Order ' . $order['reference'], ['order' => $order]);
    }

    public function resendOrder(string $reference): void
    {
        $order = (new Order())->findForCustomer($reference, (int) $this->customer['id']);

        if ($order === null || !Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->back('/account/orders', 'Something went wrong. Please try again.');
        }

        $sent = Mailer::sendOrderConfirmation($order);
        $this->back('/account/orders/' . rawurlencode($order['reference']), $sent
            ? 'We\'ve re-sent your order confirmation to ' . $order['customer_email'] . '.'
            : 'Sorry, we couldn\'t send the email right now. Please try again later.');
    }

    public function donations(): void
    {
        $this->render('account/donations', 'My Donations', [
            'donations' => (new Donation())->listForCustomer((int) $this->customer['id']),
        ]);
    }

    public function resendDonation(string $id): void
    {
        $donation = (new Donation())->findForCustomer((int) $id, (int) $this->customer['id']);

        if ($donation === null || !Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->back('/account/donations', 'Something went wrong. Please try again.');
        }

        $sent = Mailer::sendDonationReceipt($donation);
        $this->back('/account/donations', $sent
            ? 'We\'ve re-sent your receipt to ' . $donation['donor_email'] . '.'
            : 'Sorry, we couldn\'t send the email right now. Please try again later.');
    }

    public function wishlist(): void
    {
        if (!Shop::enabled()) {
            (new ErrorController())->notFound();

            return;
        }

        $this->render('account/wishlist', 'My Wishlist', [
            'products' => (new WishlistItem())->products((int) $this->customer['id']),
        ]);
    }

    public function moveToCart(string $productId): void
    {
        if (!Shop::enabled() || !Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->back('/account/wishlist', 'Something went wrong. Please try again.');
        }

        $product = (new Product())->find((int) $productId);
        $inCart = Cart::items()[(int) $productId] ?? 0;

        if ($product === null || (int) $product['is_active'] !== 1 || (int) $product['stock'] <= $inCart) {
            $this->back('/account/wishlist', 'Sorry, that item is out of stock right now.');
        }

        Cart::add((int) $product['id'], 1);
        (new WishlistItem())->remove((int) $this->customer['id'], (int) $product['id']);
        $_SESSION['shop_notice'] = $product['name'] . ' moved to your cart.';
        header('Location: /cart');
        exit;
    }

    public function resendVerification(): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null) && $this->customer['email_verified_at'] === null) {
            $sent = CustomerLinks::send($this->customer, 'verify');
            $this->back('/account', $sent
                ? 'We\'ve sent a new confirmation link to ' . $this->customer['email'] . '.'
                : 'Sorry, we couldn\'t send the email right now. Please try again later.');
        }

        header('Location: /account');
        exit;
    }

    private function render(string $view, string $pageTitle, array $data): void
    {
        $flash = $_SESSION['account_flash'] ?? null;
        unset($_SESSION['account_flash']);

        $this->view($view, $data + [
            'title' => $pageTitle . ' - Bloom Beyond Borders',
            'pageTitle' => $pageTitle,
            'noindex' => true,
            'customer' => $this->customer,
            'flash' => $flash,
            'csrfToken' => Csrf::token(),
        ]);
    }

    private function back(string $to, string $message): never
    {
        $_SESSION['account_flash'] = $message;
        header('Location: ' . $to);
        exit;
    }
}
