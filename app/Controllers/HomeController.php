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
            'description' => 'Bloom Beyond Borders supports vulnerable children and women in Uganda through education, healthcare, and economic opportunity, and helps African immigrant families in the USA integrate with culturally responsive guidance.',
            'teamMembers' => (new TeamMember())->latest(3),
            'partners' => (new Partner())->all(),
            'galleryImages' => (new GalleryImage())->latest(6),
        ]);
    }
}
