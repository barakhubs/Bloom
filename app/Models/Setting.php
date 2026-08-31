<?php

namespace App\Models;

use App\Core\Database;

/**
 * Simple key/value reader for the `settings` table (social links, contact info,
 * SMTP config, logo paths). Doesn't extend Model since it's not row-oriented CRUD -
 * it's read on every page load by the shared header/footer partials.
 */
class Setting
{
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT `key`, `value` FROM settings');
        $settings = [];

        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }

    public static function get(string $key, string $default = ''): string
    {
        $stmt = Database::connection()->prepare('SELECT `value` FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        return $value !== false && $value !== null ? $value : $default;
    }
}
