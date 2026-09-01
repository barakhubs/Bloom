<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $viewsDir = dirname(__DIR__) . '/Views';
        $viewPath = $viewsDir . '/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data);
        require $viewsDir . '/partials/header.php';
        require $viewPath;
        require $viewsDir . '/partials/footer.php';
    }

    /**
     * Renders a view with no automatic layout wrapping - for standalone pages
     * (e.g. the admin login screen) that manage their own complete HTML document.
     */
    protected function renderRaw(string $view, array $data = []): void
    {
        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data);
        require $viewPath;
    }
}
