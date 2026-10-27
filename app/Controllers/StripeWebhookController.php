<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\Stripe;
use App\Models\Customer;
use App\Models\Donation;
use App\Models\Order;
use App\Models\Product;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;

/**
 * Receives Stripe events. This is the one POST route without a CSRF token -
 * Stripe calls it server-to-server - so it's authenticated by the
 * Stripe-Signature header instead, and rejects anything that doesn't verify.
 *
 * Handlers must be idempotent: Stripe retries on any non-2xx and may deliver
 * an event more than once.
 */
class StripeWebhookController
{
    public function handle(): void
    {
        $payload = (string) file_get_contents('php://input');
        $signature = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');

        try {
            $event = Stripe::verifyWebhook($payload, $signature);
        } catch (\UnexpectedValueException | SignatureVerificationException $e) {
            $this->respond(400, 'Invalid payload or signature');
        } catch (\RuntimeException $e) {
            error_log('Stripe webhook: ' . $e->getMessage());
            $this->respond(500, 'Webhook not configured');
        }

        $object = $event->data->object;

        if ($object instanceof Session) {
            match ($object->metadata['type'] ?? null) {
                'donation' => $this->handleDonation($event->type, $object),
                'order' => $this->handleOrder($event->type, $object),
                default => null,
            };
        }

        $this->respond(200, 'ok');
    }

    private function handleDonation(string $eventType, Session $session): void
    {
        $donations = new Donation();

        switch ($eventType) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
                if ($session->payment_status !== 'paid') {
                    return; // delayed payment method - wait for async_payment_succeeded
                }

                $paid = $donations->markPaidBySession(
                    $session->id,
                    $this->paymentIntentId($session),
                    $session->customer_details->name ?? null,
                    $session->customer_details->email ?? null
                );

                if ($paid) {
                    $donation = $donations->findBySession($session->id);
                    if ($donation['customer_id'] === null && !empty($donation['donor_email'])) {
                        $account = (new Customer())->findVerifiedByEmail($donation['donor_email']);
                        if ($account !== null) {
                            $donations->attachCustomer((int) $donation['id'], (int) $account['id']);
                        }
                    }
                    Mailer::sendDonationReceipt($donation);
                    Mailer::sendDonationNotification($donation);
                }
                break;

            case 'checkout.session.async_payment_failed':
                $donations->markStatusBySession($session->id, 'failed');
                break;

            case 'checkout.session.expired':
                $donations->markStatusBySession($session->id, 'expired');
                break;
        }
    }

    private function handleOrder(string $eventType, Session $session): void
    {
        $orders = new Order();

        switch ($eventType) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
                if ($session->payment_status !== 'paid') {
                    return;
                }

                $customer = $session->customer_details;
                // Current API versions put the address under collected_information; older ones had shipping_details.
                $shipping = $session->collected_information->shipping_details ?? $session->shipping_details ?? null;
                $address = $shipping->address ?? null;

                $paid = $orders->markPaidBySession($session->id, $this->paymentIntentId($session), [
                    'name' => $customer->name ?? null,
                    'email' => $customer->email ?? null,
                    'phone' => $customer->phone ?? null,
                    'ship_name' => $shipping->name ?? null,
                    'line1' => $address->line1 ?? null,
                    'line2' => $address->line2 ?? null,
                    'city' => $address->city ?? null,
                    'state' => $address->state ?? null,
                    'postal_code' => $address->postal_code ?? null,
                    'country' => $address->country ?? null,
                ]);

                if (!$paid) {
                    return; // already processed (retry/replay) or unknown session
                }

                $order = $orders->findBySession($session->id);

                if ($order['customer_id'] === null && !empty($order['customer_email'])) {
                    $account = (new Customer())->findVerifiedByEmail($order['customer_email']);
                    if ($account !== null) {
                        $orders->attachCustomer((int) $order['id'], (int) $account['id']);
                    }
                }

                if ((int) $session->amount_total !== (int) $order['total_cents']) {
                    error_log("Stripe webhook: order {$order['reference']} paid {$session->amount_total} but total is {$order['total_cents']}");
                }

                $products = new Product();
                $soldOut = [];
                foreach ($order['items'] as $item) {
                    if ($item['product_id'] !== null && $products->decrementStock((int) $item['product_id'], (int) $item['quantity']) === 0) {
                        $soldOut[] = $item['product_name'];
                    }
                }

                Mailer::sendOrderConfirmation($order);
                Mailer::sendOrderNotification($order, $soldOut);
                break;

            case 'checkout.session.expired':
                $orders->markExpiredBySession($session->id);
                break;
        }
    }

    private function paymentIntentId(Session $session): ?string
    {
        return is_string($session->payment_intent) ? $session->payment_intent : ($session->payment_intent->id ?? null);
    }

    private function respond(int $status, string $message): never
    {
        http_response_code($status);
        header('Content-Type: text/plain');
        echo $message;
        exit;
    }
}
