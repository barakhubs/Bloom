<?php

namespace App\Core;

abstract class AdminController extends Controller
{
    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: /admin/login');
            exit;
        }
    }

    protected function view(string $view, array $data = []): void
    {
        $viewsDir = dirname(__DIR__) . '/Views';
        $viewPath = $viewsDir . '/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data);
        require $viewsDir . '/admin/partials/header.php';
        require $viewPath;
        require $viewsDir . '/admin/partials/footer.php';
    }
}
