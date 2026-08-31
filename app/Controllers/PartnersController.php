<?php

namespace App\Controllers;

use App\Core\Controller;

class PartnersController extends Controller
{
    public function index(): void
    {
        $this->view('partners/index', [
            'title' => 'Partners - Bloom Beyond Borders',
            'pageTitle' => 'Partners',
        ]);
    }
}
