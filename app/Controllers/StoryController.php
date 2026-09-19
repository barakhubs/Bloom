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
            'description' => 'Meet founder Dr. Agatha Asiimwe and learn how Bloom Beyond Borders is breaking cycles of poverty in Uganda and supporting African immigrant families in the USA through education, healthcare, and economic opportunity.',
            'stats' => Story::stats(),
            'financeEntriesByYear' => Story::financeEntriesByYear(),
            'boardLetter' => Story::boardLetter(),
        ]);
    }
}
