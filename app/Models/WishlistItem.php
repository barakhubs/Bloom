<?php

namespace App\Models;

use App\Core\Model;

class WishlistItem extends Model
{
    /** Adds or removes the product; returns true if it's now in the wishlist. */
    public function toggle(int $customerId, int $productId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM wishlist_items WHERE customer_id = ? AND product_id = ?');
        $stmt->execute([$customerId, $productId]);

        if ($stmt->rowCount() > 0) {
            return false;
        }

        $stmt = $this->db->prepare('INSERT IGNORE INTO wishlist_items (customer_id, product_id) VALUES (?, ?)');
        $stmt->execute([$customerId, $productId]);

        return true;
    }

    public function remove(int $customerId, int $productId): void
    {
        $stmt = $this->db->prepare('DELETE FROM wishlist_items WHERE customer_id = ? AND product_id = ?');
        $stmt->execute([$customerId, $productId]);
    }

    /** @return int[] product ids in this customer's wishlist */
    public function productIds(int $customerId): array
    {
        $stmt = $this->db->prepare('SELECT product_id FROM wishlist_items WHERE customer_id = ?');
        $stmt->execute([$customerId]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** Wishlisted products that are still active, newest first. */
    public function products(int $customerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.* FROM wishlist_items w
             JOIN products p ON p.id = w.product_id
             WHERE w.customer_id = ? AND p.is_active = 1
             ORDER BY w.created_at DESC'
        );
        $stmt->execute([$customerId]);

        return $stmt->fetchAll();
    }
}
