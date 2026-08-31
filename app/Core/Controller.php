<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data);
        require $viewPath;
    }
}
