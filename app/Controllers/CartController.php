<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Shop;
use App\Core\Stripe;
use App\Models\Product;

class CartController extends Controller
{
    public function __construct()
    {
        if (!Shop::enabled()) {
            (new ErrorController())->notFound();
            exit;
        }
    }

    public function index(): void
    {
        $cart = Cart::resolve();
        $notice = $_SESSION['shop_notice'] ?? null;
        unset($_SESSION['shop_notice']);

        if ($cart['adjusted'] && $notice === null) {
            $notice = 'Some items in your cart changed availability, so we\'ve updated the quantities.';
        }

        $shippingCents = empty($cart['lines']) ? 0 : Shop::shippingCents();

        $this->view('cart/index', [
            'title' => 'Your Cart - Bloom Beyond Borders',
            'pageTitle' => 'Your Cart',
            'noindex' => true,
            'cart' => $cart,
            'shippingCents' => $shippingCents,
            'totalCents' => $cart['subtotal_cents'] + $shippingCents,
            'notice' => $notice,
            'csrfToken' => Csrf::token(),
            'paymentsEnabled' => Stripe::isConfigured(),
        ]);
    }

    public function add(): void
    {
        $this->guardCsrf();

        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $product = (new Product())->find($productId);

        if ($product === null || (int) $product['is_active'] !== 1) {
            $this->back('/shop', 'That product is no longer available.');
        }

        $alreadyInCart = Cart::items()[$productId] ?? 0;
        $canAdd = (int) $product['stock'] - $alreadyInCart;

        if ($canAdd <= 0) {
            $this->back('/shop/' . $product['slug'], 'Sorry, there\'s no more stock of ' . $product['name'] . ' available.');
        }

        Cart::add($productId, min($quantity, $canAdd));
        $_SESSION['shop_notice'] = $quantity > $canAdd
            ? 'Only ' . $canAdd . ' more ' . $product['name'] . ' available; we\'ve added what we have.'
            : $product['name'] . ' added to your cart.';

        header('Location: /cart');
        exit;
    }

    public function update(): void
    {
        $this->guardCsrf();

        foreach ((array) ($_POST['quantities'] ?? []) as $productId => $quantity) {
            Cart::set((int) $productId, (int) $quantity);
        }

        header('Location: /cart');
        exit;
    }

    public function remove(): void
    {
        $this->guardCsrf();

        Cart::set((int) ($_POST['product_id'] ?? 0), 0);

        header('Location: /cart');
        exit;
    }

    private function guardCsrf(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->back('/cart', 'Your session expired. Please try again.');
        }
    }

    private function back(string $to, string $notice): never
    {
        $_SESSION['shop_notice'] = $notice;
        header('Location: ' . $to);
        exit;
    }
}
