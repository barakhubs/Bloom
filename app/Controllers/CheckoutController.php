<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Money;
use App\Core\Shop;
use App\Core\Stripe;
use App\Models\Order;
use Stripe\Exception\ApiErrorException;

/**
 * Guest checkout: no account needed. Stripe Checkout collects the email,
 * phone, and shipping address; the order confirmation is emailed once the
 * webhook confirms payment.
 */
class CheckoutController extends Controller
{
    /** Checkout Sessions for cart orders expire after 30 minutes (Stripe's minimum). */
    private const SESSION_TTL_SECONDS = 1800;

    public function __construct()
    {
        if (!Shop::enabled()) {
            (new ErrorController())->notFound();
            exit;
        }
    }

    public function start(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->backToCart('Your session expired. Please try again.');
        }

        // Re-validate against live product data: prices, availability and stock
        // all come from the database here, never from the browser.
        $cart = Cart::resolve();

        if ($cart['adjusted']) {
            $this->backToCart('Some items in your cart changed availability. Please review your cart and check out again.');
        }
        if (empty($cart['lines'])) {
            $this->backToCart('Your cart is empty.');
        }

        $client = Stripe::client();
        $countries = Shop::shipCountries();

        if ($client === null || empty($countries)) {
            $this->backToCart('Checkout is temporarily unavailable. Please try again later or contact us.');
        }

        $items = [];
        $lineItems = [];
        foreach ($cart['lines'] as $line) {
            $product = $line['product'];
            $items[] = [
                'product_id' => (int) $product['id'],
                'name' => $product['name'],
                'unit_price_cents' => (int) $product['price_cents'],
                'quantity' => $line['quantity'],
            ];
            $lineItems[] = [
                'quantity' => $line['quantity'],
                'price_data' => [
                    'currency' => Money::CURRENCY,
                    'unit_amount' => (int) $product['price_cents'],
                    'product_data' => ['name' => $product['name']],
                ],
            ];
        }

        $shippingCents = Shop::shippingCents();
        $orders = new Order();
        $order = $orders->createPending($items, $shippingCents, Money::CURRENCY);
        $metadata = ['type' => 'order', 'order_id' => (string) $order['id'], 'order_reference' => $order['reference']];

        try {
            $session = $client->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => $lineItems,
                'shipping_address_collection' => ['allowed_countries' => $countries],
                'shipping_options' => [[
                    'shipping_rate_data' => [
                        'type' => 'fixed_amount',
                        'display_name' => $shippingCents > 0 ? 'Standard shipping' : 'Free shipping',
                        'fixed_amount' => ['amount' => $shippingCents, 'currency' => Money::CURRENCY],
                    ],
                ]],
                'phone_number_collection' => ['enabled' => true],
                'client_reference_id' => $order['reference'],
                'metadata' => $metadata,
                'payment_intent_data' => ['description' => 'Order ' . $order['reference'], 'metadata' => $metadata],
                'expires_at' => time() + self::SESSION_TTL_SECONDS,
                'success_url' => Stripe::url('/checkout/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => Stripe::url('/checkout/cancel?session_id={CHECKOUT_SESSION_ID}'),
            ]);
        } catch (ApiErrorException $e) {
            error_log('Stripe: could not create order checkout session - ' . $e->getMessage());
            $orders->deletePending((int) $order['id']);
            $this->backToCart('We couldn\'t start checkout just now. Please try again in a moment.');
        }

        $orders->attachSession((int) $order['id'], $session->id);

        header('Location: ' . $session->url, true, 303);
        exit;
    }

    /** Display-only confirmation; the webhook is what marks the order paid. */
    public function success(): void
    {
        $sessionId = (string) ($_GET['session_id'] ?? '');
        $order = $this->validSessionId($sessionId) ? (new Order())->findBySession($sessionId) : null;

        if ($order === null) {
            header('Location: /cart');
            exit;
        }

        $confirmed = in_array($order['status'], ['paid', 'shipped'], true);
        $email = $order['customer_email'];

        // The redirect usually beats the webhook - ask Stripe so the buyer sees a confirmation.
        if (!$confirmed && $order['status'] === 'pending' && ($client = Stripe::client()) !== null) {
            try {
                $session = $client->checkout->sessions->retrieve($sessionId);
                $confirmed = $session->payment_status === 'paid';
                $email = $email ?: ($session->customer_details->email ?? null);
            } catch (ApiErrorException $e) {
                error_log('Stripe: could not retrieve order session - ' . $e->getMessage());
            }
        }

        if ($confirmed) {
            Cart::clear();
        }

        $this->view('checkout/success', [
            'title' => 'Order Received - Bloom Beyond Borders',
            'pageTitle' => 'Thank You',
            'noindex' => true,
            'order' => $order,
            'confirmed' => $confirmed,
            'email' => $email,
        ]);
    }

    /**
     * Buyer clicked "back" on Stripe's page. Expire that session straight away
     * so it can't be paid later; the expired webhook then closes the pending order.
     */
    public function cancel(): void
    {
        $sessionId = (string) ($_GET['session_id'] ?? '');
        $order = $this->validSessionId($sessionId) ? (new Order())->findBySession($sessionId) : null;

        if ($order !== null && $order['status'] === 'pending' && ($client = Stripe::client()) !== null) {
            try {
                $client->checkout->sessions->expire($sessionId);
            } catch (ApiErrorException $e) {
                // Already completed or expired - nothing to do.
            }
        }

        $this->backToCart('Checkout cancelled. Your cart is still here whenever you\'re ready.');
    }

    private function validSessionId(string $sessionId): bool
    {
        return preg_match('/^cs_[A-Za-z0-9_]+$/', $sessionId) === 1;
    }

    private function backToCart(string $notice): never
    {
        $_SESSION['shop_notice'] = $notice;
        header('Location: /cart');
        exit;
    }
}
