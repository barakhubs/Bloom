<?php

namespace App\Models;

use App\Core\Model;

class AdminUser extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admin_users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admin_users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }
}
