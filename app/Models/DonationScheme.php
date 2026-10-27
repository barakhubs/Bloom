<?php

namespace App\Models;

use App\Core\Model;

/**
 * Donation campaigns. `preset_amounts` is a CSV of cents (e.g. "2500,5000");
 * use presets() to read it as an int list.
 */
class DonationScheme extends Model
{
    /** Every scheme with its paid total (raised_cents), for the admin list. */
    public function all(): array
    {
        return $this->db->query(
            "SELECT s.*, COALESCE(SUM(CASE WHEN d.status = 'paid' THEN d.amount_cents END), 0) AS raised_cents
             FROM donation_schemes s
             LEFT JOIN donations d ON d.scheme_id = s.id
             GROUP BY s.id
             ORDER BY s.sort_order ASC, s.id ASC"
        )->fetchAll();
    }

    /** Active schemes with their paid total (raised_cents), for the public /donate page. */
    public function active(): array
    {
        return $this->db->query(
            "SELECT s.*, COALESCE(SUM(CASE WHEN d.status = 'paid' THEN d.amount_cents END), 0) AS raised_cents
             FROM donation_schemes s
             LEFT JOIN donations d ON d.scheme_id = s.id
             WHERE s.is_active = 1
             GROUP BY s.id
             ORDER BY s.sort_order ASC, s.id ASC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM donation_schemes WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /** An active scheme by slug, with raised_cents. */
    public function findActiveBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, COALESCE(SUM(CASE WHEN d.status = 'paid' THEN d.amount_cents END), 0) AS raised_cents
             FROM donation_schemes s
             LEFT JOIN donations d ON d.scheme_id = s.id
             WHERE s.slug = ? AND s.is_active = 1
             GROUP BY s.id"
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM donation_schemes WHERE slug = ? AND id <> ?');
        $stmt->execute([$slug, $exceptId ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** @param array{name: string, slug: string, summary: ?string, description: ?string, preset_amounts: string, allow_custom_amount: int, goal_cents: ?int, is_active: int, sort_order: int} $data */
    public function create(array $data, ?string $imagePath): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO donation_schemes
                (name, slug, summary, description, image_path, preset_amounts, allow_custom_amount, goal_cents, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'], $data['slug'], $data['summary'], $data['description'], $imagePath,
            $data['preset_amounts'], $data['allow_custom_amount'], $data['goal_cents'], $data['is_active'], $data['sort_order'],
        ]);
    }

    /** Same $data shape as create(); $imagePath null keeps the current image. */
    public function update(int $id, array $data, ?string $imagePath): void
    {
        $params = [
            $data['name'], $data['slug'], $data['summary'], $data['description'],
            $data['preset_amounts'], $data['allow_custom_amount'], $data['goal_cents'], $data['is_active'], $data['sort_order'],
        ];
        $imageSql = '';

        if ($imagePath !== null) {
            $imageSql = ', image_path = ?';
            $params[] = $imagePath;
        }

        $params[] = $id;
        $stmt = $this->db->prepare(
            'UPDATE donation_schemes
             SET name = ?, slug = ?, summary = ?, description = ?, preset_amounts = ?, allow_custom_amount = ?,
                 goal_cents = ?, is_active = ?, sort_order = ?' . $imageSql . '
             WHERE id = ?'
        );
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM donation_schemes WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** @return int[] preset amounts in cents */
    public static function presets(array $scheme): array
    {
        $csv = trim((string) ($scheme['preset_amounts'] ?? ''));

        return $csv === '' ? [] : array_map('intval', explode(',', $csv));
    }
}
