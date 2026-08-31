<?php

namespace App\Core;

use App\Controllers\ErrorController;

class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: array{0: class-string, 1: string}}> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $this->toPattern($path),
            'handler' => $handler,
        ];
    }

    private function toPattern(string $path): string
    {
        $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $path);

        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                [$controllerClass, $action] = $route['handler'];
                $controller = new $controllerClass();
                $controller->$action(...$matches);

                return;
            }
        }

        (new ErrorController())->notFound();
    }
}
