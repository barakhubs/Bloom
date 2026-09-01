<?php

declare(strict_types=1);

session_start();

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\PartnersController as AdminPartnersController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Controllers\Admin\TeamController as AdminTeamController;
use App\Controllers\BlogController;
use App\Controllers\ContactController;
use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\PartnersController;
use App\Controllers\ServicesController;
use App\Controllers\StoryController;
use App\Controllers\TeamController;
use App\Core\Router;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/our-story', [StoryController::class, 'index']);
$router->get('/our-services', [ServicesController::class, 'index']);
$router->get('/our-team', [TeamController::class, 'index']);
$router->get('/partners', [PartnersController::class, 'index']);
$router->get('/gallery', [GalleryController::class, 'index']);
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/{slug}', [BlogController::class, 'show']);
$router->post('/blog/{slug}/comments', [BlogController::class, 'submitComment']);
$router->get('/contact', [ContactController::class, 'index']);
$router->post('/contact', [ContactController::class, 'submit']);

$router->get('/admin/login', [AdminAuthController::class, 'showLogin']);
$router->post('/admin/login', [AdminAuthController::class, 'login']);
$router->post('/admin/logout', [AdminAuthController::class, 'logout']);
$router->get('/admin', [AdminDashboardController::class, 'index']);
$router->get('/admin/settings', [AdminSettingsController::class, 'index']);
$router->post('/admin/settings', [AdminSettingsController::class, 'update']);

$router->get('/admin/team', [AdminTeamController::class, 'index']);
$router->get('/admin/team/create', [AdminTeamController::class, 'create']);
$router->post('/admin/team', [AdminTeamController::class, 'store']);
$router->get('/admin/team/{id}/edit', [AdminTeamController::class, 'edit']);
$router->post('/admin/team/{id}', [AdminTeamController::class, 'update']);
$router->post('/admin/team/{id}/delete', [AdminTeamController::class, 'destroy']);

$router->get('/admin/partners', [AdminPartnersController::class, 'index']);
$router->get('/admin/partners/create', [AdminPartnersController::class, 'create']);
$router->post('/admin/partners', [AdminPartnersController::class, 'store']);
$router->get('/admin/partners/{id}/edit', [AdminPartnersController::class, 'edit']);
$router->post('/admin/partners/{id}', [AdminPartnersController::class, 'update']);
$router->post('/admin/partners/{id}/delete', [AdminPartnersController::class, 'destroy']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
