<?php

namespace App\Models;

use App\Core\Model;

class Partner extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM partners ORDER BY sort_order ASC, id ASC')->fetchAll();
    }
}
