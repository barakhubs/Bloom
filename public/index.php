<?php

declare(strict_types=1);

$appConfig = require dirname(__DIR__) . '/app/Config/app.php';

error_reporting(E_ALL);
if ($appConfig['debug']) {
    ini_set('display_errors', '1');
} else {
    // Never leak stack traces, file paths, or raw DB errors to visitors - log
    // the real error server-side and show a generic message instead.
    ini_set('display_errors', '0');
    set_exception_handler(function (\Throwable $e): void {
        error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        echo 'Something went wrong. Please try again later.';
    });
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\Admin\AccountController as AdminAccountController;
use App\Controllers\AccountController;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\BlogController as AdminBlogController;
use App\Controllers\Admin\CommentsController as AdminCommentsController;
use App\Controllers\Admin\ContactController as AdminContactController;
use App\Controllers\Admin\CustomersController as AdminCustomersController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\DonationSchemesController as AdminDonationSchemesController;
use App\Controllers\Admin\DonationsController as AdminDonationsController;
use App\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Controllers\Admin\OrdersController as AdminOrdersController;
use App\Controllers\Admin\PartnersController as AdminPartnersController;
use App\Controllers\Admin\ProductsController as AdminProductsController;
use App\Controllers\Admin\RolesController as AdminRolesController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Controllers\Admin\StoryController as AdminStoryController;
use App\Controllers\Admin\TeamController as AdminTeamController;
use App\Controllers\Admin\UsersController as AdminUsersController;
use App\Controllers\BlogController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\ContactController;
use App\Controllers\CustomerAuthController;
use App\Controllers\DonationController;
use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\PartnersController;
use App\Controllers\ProfileController;
use App\Controllers\ServicesController;
use App\Controllers\ShopController;
use App\Controllers\SitemapController;
use App\Controllers\StoryController;
use App\Controllers\StripeWebhookController;
use App\Controllers\TeamController;
use App\Controllers\WishlistController;
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
$router->get('/donate', [DonationController::class, 'index']);
$router->post('/donate/checkout', [DonationController::class, 'checkout']);
$router->get('/donate/thank-you', [DonationController::class, 'thankYou']);
$router->get('/donate/{slug}', [DonationController::class, 'show']);
$router->get('/shop', [ShopController::class, 'index']);
$router->get('/shop/{slug}', [ShopController::class, 'show']);
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->post('/checkout', [CheckoutController::class, 'start']);
$router->get('/checkout/success', [CheckoutController::class, 'success']);
$router->get('/checkout/cancel', [CheckoutController::class, 'cancel']);
$router->post('/wishlist/{productId}/toggle', [WishlistController::class, 'toggle']);

$router->get('/account/register', [CustomerAuthController::class, 'showRegister']);
$router->post('/account/register', [CustomerAuthController::class, 'register']);
$router->get('/account/check-email', [CustomerAuthController::class, 'showCheckEmail']);
$router->get('/account/verify/{token}', [CustomerAuthController::class, 'verify']);
$router->get('/account/login', [CustomerAuthController::class, 'showLogin']);
$router->post('/account/login', [CustomerAuthController::class, 'login']);
$router->post('/account/logout', [CustomerAuthController::class, 'logout']);
$router->get('/account/forgot', [CustomerAuthController::class, 'showForgot']);
$router->post('/account/forgot', [CustomerAuthController::class, 'forgot']);
$router->get('/account/reset/{token}', [CustomerAuthController::class, 'showReset']);
$router->post('/account/reset/{token}', [CustomerAuthController::class, 'reset']);
$router->get('/account', [AccountController::class, 'index']);
$router->post('/account/verify/resend', [AccountController::class, 'resendVerification']);
$router->get('/account/orders', [AccountController::class, 'orders']);
$router->get('/account/orders/{reference}', [AccountController::class, 'order']);
$router->post('/account/orders/{reference}/resend', [AccountController::class, 'resendOrder']);
$router->get('/account/donations', [AccountController::class, 'donations']);
$router->post('/account/donations/{id}/resend', [AccountController::class, 'resendDonation']);
$router->get('/account/wishlist', [AccountController::class, 'wishlist']);
$router->post('/account/wishlist/{productId}/move-to-cart', [AccountController::class, 'moveToCart']);
$router->get('/account/profile', [ProfileController::class, 'index']);
$router->post('/account/profile', [ProfileController::class, 'update']);
$router->post('/account/email', [ProfileController::class, 'changeEmail']);
$router->post('/account/password', [ProfileController::class, 'changePassword']);
$router->post('/account/delete', [ProfileController::class, 'destroy']);
// Server-to-server from Stripe: authenticated by the Stripe-Signature header, not CSRF.
$router->post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
$router->get('/sitemap.xml', [SitemapController::class, 'index']);

$router->get('/admin/login', [AdminAuthController::class, 'showLogin']);
$router->post('/admin/login', [AdminAuthController::class, 'login']);
$router->post('/admin/logout', [AdminAuthController::class, 'logout']);
$router->get('/admin/forgot-password', [AdminAuthController::class, 'showForgotPassword']);
$router->post('/admin/forgot-password', [AdminAuthController::class, 'forgotPassword']);
$router->get('/admin/set-password/{token}', [AdminAuthController::class, 'showSetPassword']);
$router->post('/admin/set-password/{token}', [AdminAuthController::class, 'setPassword']);
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

$router->get('/admin/comments', [AdminCommentsController::class, 'index']);
$router->post('/admin/comments/{id}/approve', [AdminCommentsController::class, 'approve']);
$router->post('/admin/comments/{id}/delete', [AdminCommentsController::class, 'destroy']);

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

$router->get('/admin/donations', [AdminDonationsController::class, 'index']);
$router->get('/admin/donation-schemes', [AdminDonationSchemesController::class, 'index']);
$router->get('/admin/donation-schemes/create', [AdminDonationSchemesController::class, 'create']);
$router->post('/admin/donation-schemes', [AdminDonationSchemesController::class, 'store']);
$router->get('/admin/donation-schemes/{id}/edit', [AdminDonationSchemesController::class, 'edit']);
$router->post('/admin/donation-schemes/{id}', [AdminDonationSchemesController::class, 'update']);
$router->post('/admin/donation-schemes/{id}/delete', [AdminDonationSchemesController::class, 'destroy']);

$router->get('/admin/products', [AdminProductsController::class, 'index']);
$router->get('/admin/products/create', [AdminProductsController::class, 'create']);
$router->post('/admin/products', [AdminProductsController::class, 'store']);
$router->get('/admin/products/{id}/edit', [AdminProductsController::class, 'edit']);
$router->post('/admin/products/{id}', [AdminProductsController::class, 'update']);
$router->post('/admin/products/{id}/delete', [AdminProductsController::class, 'destroy']);

$router->get('/admin/orders', [AdminOrdersController::class, 'index']);
$router->get('/admin/orders/{id}', [AdminOrdersController::class, 'show']);
$router->post('/admin/orders/{id}/ship', [AdminOrdersController::class, 'ship']);
$router->post('/admin/orders/{id}/cancel', [AdminOrdersController::class, 'cancel']);

$router->get('/admin/customers', [AdminCustomersController::class, 'index']);
$router->get('/admin/customers/{id}', [AdminCustomersController::class, 'show']);
$router->post('/admin/customers/{id}/send-reset', [AdminCustomersController::class, 'sendReset']);
$router->post('/admin/customers/{id}/delete', [AdminCustomersController::class, 'destroy']);

$router->get('/admin/users', [AdminUsersController::class, 'index']);
$router->get('/admin/users/create', [AdminUsersController::class, 'create']);
$router->post('/admin/users', [AdminUsersController::class, 'store']);
$router->get('/admin/users/{id}/edit', [AdminUsersController::class, 'edit']);
$router->post('/admin/users/{id}', [AdminUsersController::class, 'update']);
$router->post('/admin/users/{id}/delete', [AdminUsersController::class, 'destroy']);
$router->post('/admin/users/{id}/send-link', [AdminUsersController::class, 'sendLink']);

$router->get('/admin/roles', [AdminRolesController::class, 'index']);
$router->get('/admin/roles/create', [AdminRolesController::class, 'create']);
$router->post('/admin/roles', [AdminRolesController::class, 'store']);
$router->get('/admin/roles/{id}/edit', [AdminRolesController::class, 'edit']);
$router->post('/admin/roles/{id}', [AdminRolesController::class, 'update']);
$router->post('/admin/roles/{id}/delete', [AdminRolesController::class, 'destroy']);

$router->get('/admin/account', [AdminAccountController::class, 'index']);
$router->post('/admin/account', [AdminAccountController::class, 'update']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
