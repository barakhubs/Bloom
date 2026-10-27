<?php

namespace App\Core;

use App\Models\Product;

/**
 * Session-backed shopping cart. Only product ids and quantities are stored -
 * names, prices and stock are always re-read from the database, so the cart
 * can never carry a stale or tampered price into checkout.
 */
class Cart
{
    private const KEY = 'cart';
    public const MAX_QTY = 99;

    /** @return array<int, int> product id => quantity */
    public static function items(): array
    {
        return array_map('intval', $_SESSION[self::KEY] ?? []);
    }

    public static function count(): int
    {
        return array_sum(self::items());
    }

    public static function add(int $productId, int $quantity): void
    {
        $items = self::items();
        self::set($productId, ($items[$productId] ?? 0) + $quantity);
    }

    /** Sets a line's quantity; 0 or less removes it. */
    public static function set(int $productId, int $quantity): void
    {
        $items = self::items();

        if ($quantity <= 0) {
            unset($items[$productId]);
        } else {
            $items[$productId] = min($quantity, self::MAX_QTY);
        }

        $_SESSION[self::KEY] = $items;
    }

    public static function clear(): void
    {
        unset($_SESSION[self::KEY]);
    }

    /**
     * The cart joined against live product rows. Lines for products that were
     * deleted, hidden, or sold out are dropped, and quantities are capped at
     * current stock; the session cart is corrected to match.
     *
     * @return array{lines: array<int, array{product: array, quantity: int, line_cents: int}>, subtotal_cents: int, adjusted: bool}
     */
    public static function resolve(): array
    {
        $items = self::items();
        $products = (new Product())->activeByIds(array_keys($items));
        $lines = [];
        $subtotal = 0;
        $adjusted = false;

        foreach ($items as $productId => $quantity) {
            $product = $products[$productId] ?? null;
            $available = $product !== null ? (int) $product['stock'] : 0;
            $finalQty = min($quantity, $available);

            if ($finalQty !== $quantity) {
                $adjusted = true;
                self::set($productId, $finalQty);
            }

            if ($finalQty <= 0) {
                continue;
            }

            $lineCents = (int) $product['price_cents'] * $finalQty;
            $subtotal += $lineCents;
            $lines[] = ['product' => $product, 'quantity' => $finalQty, 'line_cents' => $lineCents];
        }

        return ['lines' => $lines, 'subtotal_cents' => $subtotal, 'adjusted' => $adjusted];
    }
}
