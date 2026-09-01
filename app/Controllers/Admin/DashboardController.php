<?php

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Database;
use App\Models\AdminUser;

class DashboardController extends AdminController
{
    public function index(): void
    {
        $currentAdmin = (new AdminUser())->findById(Auth::id());
        $db = Database::connection();

        $counts = [
            'pending_comments' => (int) $db->query("SELECT COUNT(*) FROM blog_comments WHERE status = 'pending'")->fetchColumn(),
            'contact_submissions' => (int) $db->query('SELECT COUNT(*) FROM contact_submissions')->fetchColumn(),
            'team_members' => (int) $db->query('SELECT COUNT(*) FROM team_members')->fetchColumn(),
            'partners' => (int) $db->query('SELECT COUNT(*) FROM partners')->fetchColumn(),
            'gallery_albums' => (int) $db->query('SELECT COUNT(*) FROM gallery_albums')->fetchColumn(),
            'gallery_images' => (int) $db->query('SELECT COUNT(*) FROM gallery_images')->fetchColumn(),
            'published_posts' => (int) $db->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn(),
        ];

        $this->view('admin/dashboard/index', [
            'title' => 'Dashboard - Bloom Beyond Borders Admin',
            'currentAdmin' => $currentAdmin,
            'counts' => $counts,
        ]);
    }
}
