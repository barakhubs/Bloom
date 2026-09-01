<?php

declare(strict_types=1);

session_start();

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\BlogController as AdminBlogController;
use App\Controllers\Admin\ContactController as AdminContactController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Controllers\Admin\PartnersController as AdminPartnersController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Controllers\Admin\StoryController as AdminStoryController;
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

$router->get('/admin/gallery', [AdminGalleryController::class, 'index']);
$router->get('/admin/gallery/create', [AdminGalleryController::class, 'createAlbum']);
$router->post('/admin/gallery', [AdminGalleryController::class, 'storeAlbum']);
$router->get('/admin/gallery/{id}/edit', [AdminGalleryController::class, 'editAlbum']);
$router->post('/admin/gallery/{id}', [AdminGalleryController::class, 'updateAlbum']);
$router->post('/admin/gallery/{id}/delete', [AdminGalleryController::class, 'destroyAlbum']);
$router->get('/admin/gallery/{id}', [AdminGalleryController::class, 'showAlbum']);
$router->post('/admin/gallery/{albumId}/images', [AdminGalleryController::class, 'storeImage']);
$router->get('/admin/gallery/{albumId}/images/{imageId}/edit', [AdminGalleryController::class, 'editImage']);
$router->post('/admin/gallery/{albumId}/images/{imageId}', [AdminGalleryController::class, 'updateImage']);
$router->post('/admin/gallery/{albumId}/images/{imageId}/delete', [AdminGalleryController::class, 'destroyImage']);

$router->get('/admin/blog', [AdminBlogController::class, 'index']);
$router->get('/admin/blog/create', [AdminBlogController::class, 'create']);
$router->post('/admin/blog', [AdminBlogController::class, 'store']);
$router->get('/admin/blog/{id}/edit', [AdminBlogController::class, 'edit']);
$router->post('/admin/blog/{id}', [AdminBlogController::class, 'update']);
$router->post('/admin/blog/{id}/delete', [AdminBlogController::class, 'destroy']);

$router->get('/admin/comments', [AdminBlogController::class, 'commentsIndex']);
$router->post('/admin/comments/{id}/approve', [AdminBlogController::class, 'approveComment']);
$router->post('/admin/comments/{id}/delete', [AdminBlogController::class, 'deleteComment']);

$router->get('/admin/contact', [AdminContactController::class, 'index']);
$router->post('/admin/contact/{id}/delete', [AdminContactController::class, 'destroy']);

$router->get('/admin/story', [AdminStoryController::class, 'index']);
$router->get('/admin/story/stats/create', [AdminStoryController::class, 'createStat']);
$router->post('/admin/story/stats', [AdminStoryController::class, 'storeStat']);
$router->get('/admin/story/stats/{id}/edit', [AdminStoryController::class, 'editStat']);
$router->post('/admin/story/stats/{id}', [AdminStoryController::class, 'updateStat']);
$router->post('/admin/story/stats/{id}/delete', [AdminStoryController::class, 'destroyStat']);
$router->get('/admin/story/finance/create', [AdminStoryController::class, 'createFinance']);
$router->post('/admin/story/finance', [AdminStoryController::class, 'storeFinance']);
$router->get('/admin/story/finance/{id}/edit', [AdminStoryController::class, 'editFinance']);
$router->post('/admin/story/finance/{id}', [AdminStoryController::class, 'updateFinance']);
$router->post('/admin/story/finance/{id}/delete', [AdminStoryController::class, 'destroyFinance']);
$router->post('/admin/story/letter', [AdminStoryController::class, 'updateLetter']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
