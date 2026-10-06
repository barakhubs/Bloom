<?php

namespace App\Core;

abstract class AdminController extends Controller
{
    /**
     * Permission slug (see Permissions::ALL) required for every action on this
     * controller; null = any logged-in user.
     */
    protected ?string $permission = null;

    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: /admin/login');
            exit;
        }

        if ($this->permission !== null && !Auth::can($this->permission)) {
            http_response_code(403);
            $this->view('admin/errors/forbidden', [
                'title' => 'Access Denied - Bloom Beyond Borders Admin',
            ]);
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
