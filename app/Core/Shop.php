<?php

namespace App\Core;

use App\Models\Setting;

/** Admin-managed shop settings (Site Settings -> Shop). */
class Shop
{
    public static function enabled(): bool
    {
        return Setting::get('shop_enabled', '0') === '1';
    }

    /** Flat shipping charge per order, in cents. */
    public static function shippingCents(): int
    {
        return max(0, (int) Setting::get('shop_shipping_flat_cents', '0'));
    }

    /** ISO 3166-1 alpha-2 codes Stripe Checkout will accept shipping addresses for. */
    public static function shipCountries(): array
    {
        return self::parseCountries(Setting::get('shop_ship_countries', 'US'));
    }

    /** "us, ug ,KE" -> ['US', 'UG', 'KE']; anything that isn't two letters is dropped. */
    public static function parseCountries(string $csv): array
    {
        $codes = array_map(fn (string $code) => strtoupper(trim($code)), explode(',', $csv));

        return array_values(array_unique(array_filter($codes, fn (string $code) => preg_match('/^[A-Z]{2}$/', $code) === 1)));
    }
}
