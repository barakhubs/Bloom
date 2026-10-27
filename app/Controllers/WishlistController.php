<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\CustomerAuth;
use App\Core\Shop;
use App\Models\Product;
use App\Models\WishlistItem;

/** The heart button on shop pages. Signed-out visitors are sent to sign in, then back to the page they were on. */
class WishlistController extends Controller
{
    public function toggle(string $productId): void
    {
        $returnTo = CustomerAuth::safeNext($_POST['return_to'] ?? null, '/shop');

        if (!Shop::enabled() || !Csrf::verify($_POST['csrf_token'] ?? null)) {
            header('Location: ' . $returnTo);
            exit;
        }

        $customer = CustomerAuth::user();

        if ($customer === null) {
            header('Location: /account/login?next=' . rawurlencode($returnTo));
            exit;
        }

        $product = (new Product())->find((int) $productId);

        if ($product !== null && (int) $product['is_active'] === 1) {
            $added = (new WishlistItem())->toggle((int) $customer['id'], (int) $product['id']);
            $_SESSION['shop_notice'] = $product['name'] . ($added ? ' saved to your wishlist.' : ' removed from your wishlist.');
        }

        header('Location: ' . $returnTo);
        exit;
    }
}
