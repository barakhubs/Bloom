<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env loader: KEY=value lines, # comments, optional single/double quotes.
 * Variables already set in the real environment win, so a server's own config
 * always overrides the file.
 */
final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }
            $key = trim(substr($line, 0, $eq));
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }

            $value = trim(substr($line, $eq + 1));
            $quote = $value[0] ?? '';
            if (($quote === '"' || $quote === "'") && strlen($value) >= 2 && str_ends_with($value, $quote)) {
                $value = substr($value, 1, -1);
            } else {
                // Unquoted: strip a trailing " # comment".
                $value = rtrim(preg_replace('/\s+#.*$/', '', $value));
            }

            if (getenv($key) !== false) {
                continue;
            }
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}
