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
            'teamMembers' => (new TeamMember())->all(),
        ]);
    }
}
