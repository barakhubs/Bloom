<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Story;

class StoryController extends Controller
{
    public function index(): void
    {
        $this->view('story/index', [
            'title' => 'Our Story - Bloom Beyond Borders',
            'pageTitle' => 'Our Story',
            'stats' => Story::stats(),
            'financeEntriesByYear' => Story::financeEntriesByYear(),
            'boardLetter' => Story::boardLetter(),
        ]);
    }
}
