<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\Stripe;
use App\Models\Donation;
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
                    is_string($session->payment_intent) ? $session->payment_intent : ($session->payment_intent->id ?? null),
                    $session->customer_details->name ?? null,
                    $session->customer_details->email ?? null
                );

                if ($paid) {
                    $donation = $donations->findBySession($session->id);
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

    private function respond(int $status, string $message): never
    {
        http_response_code($status);
        header('Content-Type: text/plain');
        echo $message;
        exit;
    }
}
