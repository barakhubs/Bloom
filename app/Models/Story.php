<?php

namespace App\Models;

use App\Core\Database;

/**
 * Read/write access to the Our Story content blocks (story_stats,
 * story_finance_entries, story_board_letter).
 */
class Story
{
    public static function stats(): array
    {
        return Database::connection()
            ->query('SELECT * FROM story_stats ORDER BY sort_order ASC, id ASC')
            ->fetchAll();
    }

    public static function findStat(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM story_stats WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public static function createStat(string $label, string $value, int $sortOrder): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO story_stats (label, value, sort_order) VALUES (?, ?, ?)'
        );
        $stmt->execute([$label, $value, $sortOrder]);
    }

    public static function updateStat(int $id, string $label, string $value, int $sortOrder): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE story_stats SET label = ?, value = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->execute([$label, $value, $sortOrder, $id]);
    }

    public static function deleteStat(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM story_stats WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function financeEntriesByYear(): array
    {
        $rows = self::allFinanceEntries();

        $byYear = [];
        foreach ($rows as $row) {
            $byYear[$row['year']][] = $row;
        }

        return $byYear;
    }

    public static function allFinanceEntries(): array
    {
        return Database::connection()
            ->query('SELECT * FROM story_finance_entries ORDER BY year DESC, sort_order ASC, id ASC')
            ->fetchAll();
    }

    public static function findFinanceEntry(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM story_finance_entries WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public static function createFinanceEntry(int $year, string $category, float $percentage, int $sortOrder): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO story_finance_entries (year, category, percentage, sort_order) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$year, $category, $percentage, $sortOrder]);
    }

    public static function updateFinanceEntry(int $id, int $year, string $category, float $percentage, int $sortOrder): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE story_finance_entries SET year = ?, category = ?, percentage = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->execute([$year, $category, $percentage, $sortOrder, $id]);
    }

    public static function deleteFinanceEntry(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM story_finance_entries WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function boardLetter(): ?array
    {
        $stmt = Database::connection()->query('SELECT * FROM story_board_letter WHERE id = 1');
        $letter = $stmt->fetch();

        return $letter !== false ? $letter : null;
    }

    public static function updateBoardLetter(string $authorName, ?string $authorTitle, string $body): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO story_board_letter (id, author_name, author_title, body) VALUES (1, ?, ?, ?)
             ON DUPLICATE KEY UPDATE author_name = VALUES(author_name), author_title = VALUES(author_title), body = VALUES(body)'
        );
        $stmt->execute([$authorName, $authorTitle, $body]);
    }
}
