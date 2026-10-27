<?php

namespace App\Core;

use App\Models\Setting;
use Stripe\Event;
use Stripe\StripeClient;

/**
 * Thin wrapper around the Stripe SDK, configured from the admin-managed
 * `stripe_secret_key` / `stripe_webhook_secret` settings. Payments use hosted
 * Stripe Checkout, so card details never touch this server.
 */
class Stripe
{
    private static ?StripeClient $client = null;

    public static function isConfigured(): bool
    {
        return trim(Setting::get('stripe_secret_key')) !== '';
    }

    /** True when the configured key is a test-mode key (sk_test_ / rk_test_). */
    public static function isTestMode(): bool
    {
        return str_contains(Setting::get('stripe_secret_key'), '_test_');
    }

    /** A client for the configured secret key, or null (logged) if Stripe isn't set up. */
    public static function client(): ?StripeClient
    {
        if (self::$client === null) {
            $secretKey = trim(Setting::get('stripe_secret_key'));

            if ($secretKey === '') {
                error_log('Stripe: secret key not configured in settings.');

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
        $secret = trim(Setting::get('stripe_webhook_secret'));

        if ($secret === '') {
            throw new \RuntimeException('Stripe webhook secret not configured.');
        }

        return \Stripe\Webhook::constructEvent($payload, $signatureHeader, $secret);
    }

    /** Absolute URL on this site, for Checkout success/cancel redirects. */
    public static function url(string $path): string
    {
        $appConfig = require dirname(__DIR__) . '/Config/app.php';

        return rtrim($appConfig['base_url'], '/') . $path;
    }
}
