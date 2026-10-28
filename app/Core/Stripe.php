<?php

namespace App\Core;

use App\Models\Customer;
use App\Models\Setting;
use Stripe\Event;
use Stripe\StripeClient;

/**
 * Thin wrapper around the Stripe SDK. Keys come from the STRIPE_SECRET_KEY /
 * STRIPE_WEBHOOK_SECRET environment variables (.env) when set, otherwise from
 * the admin-managed `stripe_secret_key` / `stripe_webhook_secret` settings. Payments use hosted
 * Stripe Checkout, so card details never touch this server.
 */
class Stripe
{
    private static ?StripeClient $client = null;

    /** The secret key in use: STRIPE_SECRET_KEY from the environment wins over the setting. */
    public static function secretKey(): string
    {
        return trim((string) getenv('STRIPE_SECRET_KEY')) ?: trim(Setting::get('stripe_secret_key'));
    }

    /** The webhook signing secret in use: STRIPE_WEBHOOK_SECRET wins over the setting. */
    public static function webhookSecret(): string
    {
        return trim((string) getenv('STRIPE_WEBHOOK_SECRET')) ?: trim(Setting::get('stripe_webhook_secret'));
    }

    /** True when a key is set in the environment, so the admin settings field has no effect. */
    public static function keysFromEnv(): bool
    {
        return trim((string) getenv('STRIPE_SECRET_KEY')) !== '' || trim((string) getenv('STRIPE_WEBHOOK_SECRET')) !== '';
    }

    public static function isConfigured(): bool
    {
        return self::secretKey() !== '';
    }

    /** True when the configured key is a test-mode key (sk_test_ / rk_test_). */
    public static function isTestMode(): bool
    {
        return str_contains(self::secretKey(), '_test_');
    }

    /** A client for the configured secret key, or null (logged) if Stripe isn't set up. */
    public static function client(): ?StripeClient
    {
        if (self::$client === null) {
            $secretKey = self::secretKey();

            if ($secretKey === '') {
                error_log('Stripe: secret key not configured (.env or settings).');

                return null;
            }

            self::$client = new StripeClient($secretKey);
        }

        return self::$client;
    }

    /**
     * Verifies the Stripe-Signature header against the raw request body and
     * returns the parsed event. Throws \UnexpectedValueException (bad payload),
     * \Stripe\Exception\SignatureVerificationException (bad signature), or
     * \RuntimeException (webhook secret not configured).
     */
    public static function verifyWebhook(string $payload, string $signatureHeader): Event
    {
        $secret = self::webhookSecret();

        if ($secret === '') {
            throw new \RuntimeException('Stripe webhook secret not configured.');
        }

        return \Stripe\Webhook::constructEvent($payload, $signatureHeader, $secret);
    }

    /**
     * The Stripe Customer id for a signed-in account, created on first use and
     * refreshed from the profile each time, so Checkout pre-fills the email,
     * name, phone and saved shipping address. Null (logged) if Stripe fails -
     * callers then fall back to the plain guest flow.
     */
    public static function customerFor(array $customer): ?string
    {
        $client = self::client();

        if ($client === null) {
            return null;
        }

        // Blank optional fields are left out entirely (the SDK would send null as "").
        $present = fn (array $fields) => array_filter($fields, fn ($value) => $value !== null && $value !== '');
        $params = $present([
            'email' => $customer['email'],
            'name' => $customer['name'],
            'phone' => $customer['phone'],
        ]) + ['metadata' => ['customer_id' => (string) $customer['id']]];

        if (!empty($customer['ship_line1'])) {
            $params['shipping'] = $present(['name' => $customer['name'], 'phone' => $customer['phone']]) + [
                'address' => $present([
                    'line1' => $customer['ship_line1'],
                    'line2' => $customer['ship_line2'],
                    'city' => $customer['ship_city'],
                    'state' => $customer['ship_state'],
                    'postal_code' => $customer['ship_postal_code'],
                    'country' => $customer['ship_country'],
                ]),
            ];
        }

        try {
            if (!empty($customer['stripe_customer_id'])) {
                try {
                    return $client->customers->update($customer['stripe_customer_id'], $params)->id;
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    // Deleted in the Stripe dashboard, or created under other keys - make a fresh one below.
                }
            }

            $stripeCustomerId = $client->customers->create($params)->id;
            (new Customer())->setStripeCustomerId((int) $customer['id'], $stripeCustomerId);

            return $stripeCustomerId;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe: could not prepare customer #' . $customer['id'] . ' - ' . $e->getMessage());

            return null;
        }
    }

    /** Absolute URL on this site, for Checkout success/cancel redirects. */
    public static function url(string $path): string
    {
        $appConfig = require dirname(__DIR__) . '/Config/app.php';

        return rtrim($appConfig['base_url'], '/') . $path;
    }
}
