<?php

namespace App\Core;

use App\Models\Customer;

/**
 * Session auth for optional customer accounts. Uses its own session key so an
 * admin login and a customer login never affect each other.
 */
class CustomerAuth
{
    private const KEY = 'customer_id';

    /** Per-request cache of the logged-in customer row (false = looked up, none). */
    private static array|false|null $customer = null;

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION[self::KEY]) ? (int) $_SESSION[self::KEY] : null;
    }

    /** Re-read from the DB once per request, so a deleted account is signed out immediately. */
    public static function user(): ?array
    {
        if (self::$customer === null) {
            self::$customer = false;
            $id = self::id();
            $row = $id !== null ? (new Customer())->find($id) : null;

            if ($row !== null) {
                self::$customer = $row;
            } elseif ($id !== null) {
                unset($_SESSION[self::KEY]);
            }
        }

        return self::$customer === false ? null : self::$customer;
    }

    public static function isVerified(): bool
    {
        return !empty(self::user()['email_verified_at']);
    }

    public static function login(int $customerId): void
    {
        session_regenerate_id(true);
        $_SESSION[self::KEY] = $customerId;
        self::$customer = null;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::KEY]);
        session_regenerate_id(true);
        self::$customer = null;
    }

    /** Drops the cached row after the current customer's record changes. */
    public static function refresh(): void
    {
        self::$customer = null;
    }

    /** A post-login redirect target: local paths only ("/..."), never "//host" or a full URL. */
    public static function safeNext(?string $next, string $default = '/account'): string
    {
        $next = (string) $next;

        return preg_match('#^/(?![/\\\\])[^\s]*$#', $next) === 1 ? $next : $default;
    }
}
