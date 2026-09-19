<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Partner;

class PartnersController extends Controller
{
    public function index(): void
    {
        $this->view('partners/index', [
            'title' => 'Partners - Bloom Beyond Borders',
            'pageTitle' => 'Partners',
            'description' => 'The organizations partnering with Bloom Beyond Borders to expand education, healthcare, and economic opportunity for children, women, and immigrant families.',
            'partners' => (new Partner())->all(),
        ]);
    }
}
