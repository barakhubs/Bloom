<?php

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Database;

class DashboardController extends AdminController
{
    /** Count tiles: label, permission needed to see it, count query, highlight when non-zero, link. */
    private const TILES = [
        ['Pending Comments', 'comments', "SELECT COUNT(*) FROM blog_comments WHERE status = 'pending'", true, '/admin/comments'],
        ['Contact Submissions', 'contact', 'SELECT COUNT(*) FROM contact_submissions', false, '/admin/contact'],
        ['Team Members', 'team', 'SELECT COUNT(*) FROM team_members', false, '/admin/team'],
        ['Partners', 'partners', 'SELECT COUNT(*) FROM partners', false, '/admin/partners'],
        ['Gallery Albums', 'gallery', 'SELECT COUNT(*) FROM gallery_albums', false, '/admin/gallery'],
        ['Gallery Images', 'gallery', 'SELECT COUNT(*) FROM gallery_images', false, '/admin/gallery'],
        ['Published Posts', 'blog', "SELECT COUNT(*) FROM blog_posts WHERE status = 'published'", false, '/admin/blog'],
        ['Users', 'users', 'SELECT COUNT(*) FROM admin_users', false, '/admin/users'],
    ];

    public function index(): void
    {
        $db = Database::connection();
        $tiles = [];

        foreach (self::TILES as [$label, $permission, $sql, $highlightNonZero, $href]) {
            if (!Auth::can($permission)) {
                continue;
            }
            $value = (int) $db->query($sql)->fetchColumn();
            $tiles[] = [
                'label' => $label,
                'value' => $value,
                'href' => $href,
                'highlight' => $highlightNonZero && $value > 0,
            ];
        }

        $this->view('admin/dashboard/index', [
            'title' => 'Dashboard - Bloom Beyond Borders Admin',
            'currentAdmin' => Auth::user(),
            'tiles' => $tiles,
        ]);
    }
}
