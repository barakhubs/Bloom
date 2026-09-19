<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BlogPost;

class SitemapController extends Controller
{
    public function index(): void
    {
        $appConfig = require dirname(__DIR__) . '/Config/app.php';
        $baseUrl = rtrim($appConfig['base_url'], '/');

        $staticPaths = [
            '/',
            '/our-story',
            '/our-services',
            '/our-team',
            '/partners',
            '/gallery',
            '/blog',
            '/contact',
        ];

        $posts = (new BlogPost())->publishedList();

        header('Content-Type: application/xml; charset=utf-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($staticPaths as $path) {
            echo '  <url><loc>' . htmlspecialchars($baseUrl . $path) . '</loc></url>' . "\n";
        }

        foreach ($posts as $post) {
            $loc = $baseUrl . '/blog/' . $post['slug'];
            $lastmod = !empty($post['published_at']) ? date('Y-m-d', strtotime((string) $post['published_at'])) : null;
            echo '  <url><loc>' . htmlspecialchars($loc) . '</loc>'
                . ($lastmod ? '<lastmod>' . htmlspecialchars($lastmod) . '</lastmod>' : '')
                . '</url>' . "\n";
        }

        echo '</urlset>' . "\n";
    }
}
