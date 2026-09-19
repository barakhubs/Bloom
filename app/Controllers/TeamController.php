<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TeamMember;

class TeamController extends Controller
{
    public function index(): void
    {
        $this->view('team/index', [
            'title' => 'Our Team - Bloom Beyond Borders',
            'pageTitle' => 'Our Team',
            'description' => 'Meet the board of directors leading Bloom Beyond Borders\' work supporting vulnerable children and women in Uganda and African immigrant families in the USA.',
            'teamMembers' => (new TeamMember())->all(),
        ]);
    }
}
