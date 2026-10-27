<?php

namespace App\Core;

/**
 * All money is stored and passed around as integer cents (USD). These helpers
 * convert to/from what people type and read.
 */
class Money
{
    public const CURRENCY = 'usd';

    public static function format(int $cents): string
    {
        $dollars = $cents / 100;

        return '$' . ($cents % 100 === 0 ? number_format($dollars) : number_format($dollars, 2));
    }

    /** "25", "25.5", "$1,000.00" -> cents; null if it isn't a valid non-negative amount. */
    public static function parse(string $input): ?int
    {
        $clean = str_replace([',', '$', ' '], '', trim($input));

        if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $clean)) {
            return null;
        }

        return (int) round(((float) $clean) * 100);
    }

    /** "25, 50, 100" (dollars) -> [2500, 5000, 10000]; invalid or zero entries are dropped. */
    public static function parseList(string $input): array
    {
        $amounts = [];

        foreach (explode(',', $input) as $part) {
            $cents = self::parse($part);
            if ($cents !== null && $cents > 0) {
                $amounts[] = $cents;
            }
        }

        $amounts = array_values(array_unique($amounts));
        sort($amounts);

        return $amounts;
    }

    /** Dollar amount for a form input value: 2500 -> "25", 2550 -> "25.50". */
    public static function toInput(int $cents): string
    {
        return $cents % 100 === 0 ? (string) intdiv($cents, 100) : number_format($cents / 100, 2, '.', '');
    }
}
