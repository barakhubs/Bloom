<?php

declare(strict_types=1);

session_start();

require dirname(__DIR__) . '/vendor/autoload.php';

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

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
