<?php

namespace App\Models;

use App\Core\Model;

class ContactSubmission extends Model
{
    public function create(string $name, string $email, string $message): void
    {
        $stmt = $this->db->prepare('INSERT INTO contact_submissions (name, email, message) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $message]);
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM contact_submissions ORDER BY created_at DESC')->fetchAll();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM contact_submissions WHERE id = ?');
        $stmt->execute([$id]);
    }
}
