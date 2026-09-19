<?php

namespace App\Controllers;

use App\Core\Controller;

class ServicesController extends Controller
{
    public function index(): void
    {
        $this->view('services/index', [
            'title' => 'Our Services - Bloom Beyond Borders',
            'pageTitle' => 'Our Services',
            'description' => 'Explore our programs: education support and early childhood education in Uganda, and culturally responsive family engagement for African immigrant families in the USA.',
        ]);
    }
}
