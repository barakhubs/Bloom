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
}
