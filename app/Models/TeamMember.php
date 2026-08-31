<?php

namespace App\Models;

use App\Core\Model;

class TeamMember extends Model
{
    public function latest(int $limit = 3): array
    {
        $stmt = $this->db->prepare('SELECT * FROM team_members ORDER BY sort_order ASC, id ASC LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM team_members ORDER BY sort_order ASC, id ASC')->fetchAll();
    }
}
