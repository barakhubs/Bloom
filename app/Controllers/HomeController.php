<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\GalleryImage;
use App\Models\Partner;
use App\Models\TeamMember;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home', [
            'title' => 'Bloom Beyond Borders - Transforming Lives with Knowledge, Care, and Courage',
            'teamMembers' => (new TeamMember())->latest(3),
            'partners' => (new Partner())->all(),
            'galleryImages' => (new GalleryImage())->latest(6),
        ]);
    }
}
