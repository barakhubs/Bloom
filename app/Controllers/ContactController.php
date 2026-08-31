<?php

namespace App\Controllers;

use App\Core\Controller;

class ContactController extends Controller
{
    public function index(): void
    {
        $this->view('contact/index', [
            'title' => 'Contact - Bloom Beyond Borders',
            'pageTitle' => 'Contact',
        ]);
    }
}
