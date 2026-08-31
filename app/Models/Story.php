<?php

namespace App\Models;

use App\Core\Database;

/**
 * Read-only access to the Our Story content blocks (story_stats,
 * story_finance_entries, story_board_letter). CRUD for editing these from the
 * back office lands in feature/admin-contact-story-crud.
 */
class Story
{
    public static function stats(): array
    {
        return Database::connection()
            ->query('SELECT * FROM story_stats ORDER BY sort_order ASC, id ASC')
            ->fetchAll();
    }

    public static function financeEntriesByYear(): array
    {
        $rows = Database::connection()
            ->query('SELECT * FROM story_finance_entries ORDER BY year DESC, sort_order ASC, id ASC')
            ->fetchAll();

        $byYear = [];
        foreach ($rows as $row) {
            $byYear[$row['year']][] = $row;
        }

        return $byYear;
    }

    public static function boardLetter(): ?array
    {
        $stmt = Database::connection()->query('SELECT * FROM story_board_letter WHERE id = 1');
        $letter = $stmt->fetch();

        return $letter !== false ? $letter : null;
    }
}
