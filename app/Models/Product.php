<?php

namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM products ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public function active(): array
    {
        return $this->db->query('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function findActiveBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE slug = ? AND is_active = 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Active products keyed by id, for the given ids (missing/inactive ones are
     * simply absent). The catalogue is small, so this filters the active list in
     * PHP rather than building a dynamic IN (...) clause.
     */
    public function activeByIds(array $ids): array
    {
        $wanted = array_flip(array_map('intval', $ids));
        $products = [];

        foreach ($this->active() as $row) {
            if (isset($wanted[(int) $row['id']])) {
                $products[(int) $row['id']] = $row;
            }
        }

        return $products;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?');
        $stmt->execute([$slug, $exceptId ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** @param array{name: string, slug: string, summary: ?string, description: ?string, price_cents: int, stock: int, is_active: int, sort_order: int} $data */
    public function create(array $data, ?string $imagePath): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (name, slug, summary, description, price_cents, image_path, stock, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'], $data['slug'], $data['summary'], $data['description'], $data['price_cents'],
            $imagePath, $data['stock'], $data['is_active'], $data['sort_order'],
        ]);
    }

    /** Same $data shape as create(); $imagePath null keeps the current image. */
    public function update(int $id, array $data, ?string $imagePath): void
    {
        $params = [
            $data['name'], $data['slug'], $data['summary'], $data['description'], $data['price_cents'],
            $data['stock'], $data['is_active'], $data['sort_order'],
        ];
        $imageSql = '';

        if ($imagePath !== null) {
            $imageSql = ', image_path = ?';
            $params[] = $imagePath;
        }

        $params[] = $id;
        $stmt = $this->db->prepare(
            'UPDATE products
             SET name = ?, slug = ?, summary = ?, description = ?, price_cents = ?, stock = ?, is_active = ?, sort_order = ?' . $imageSql . '
             WHERE id = ?'
        );
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** Never goes below zero (a sale that oversold just leaves stock at 0). Returns the new stock. */
    public function decrementStock(int $id, int $quantity): int
    {
        $stmt = $this->db->prepare('UPDATE products SET stock = GREATEST(CAST(stock AS SIGNED) - ?, 0) WHERE id = ?');
        $stmt->execute([$quantity, $id]);

        $stmt = $this->db->prepare('SELECT stock FROM products WHERE id = ?');
        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn();
    }

    public function incrementStock(int $id, int $quantity): void
    {
        $stmt = $this->db->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
        $stmt->execute([$quantity, $id]);
    }
}
