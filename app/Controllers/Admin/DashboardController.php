<?php

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Auth;
use App\Models\AdminUser;

class DashboardController extends AdminController
{
    public function index(): void
    {
        $currentAdmin = (new AdminUser())->findById(Auth::id());

        $this->view('admin/dashboard/index', [
            'title' => 'Dashboard - Bloom Beyond Borders Admin',
            'currentAdmin' => $currentAdmin,
        ]);
    }
}
