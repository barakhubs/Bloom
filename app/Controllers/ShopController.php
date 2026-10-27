<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Shop;
use App\Models\Product;

class ShopController extends Controller
{
    public function index(): void
    {
        if (!Shop::enabled()) {
            (new ErrorController())->notFound();

            return;
        }

        $this->view('shop/index', [
            'title' => 'Shop - Bloom Beyond Borders',
            'pageTitle' => 'Shop',
            'description' => 'Shop handmade goods and Bloom Beyond Borders merchandise. Every purchase supports our programmes in Uganda and the USA.',
            'products' => (new Product())->active(),
            'csrfToken' => Csrf::token(),
            'notice' => $this->takeNotice(),
        ]);
    }

    public function show(string $slug): void
    {
        $product = Shop::enabled() ? (new Product())->findActiveBySlug($slug) : null;

        if ($product === null) {
            (new ErrorController())->notFound();

            return;
        }

        $this->view('shop/show', [
            'title' => $product['name'] . ' - Shop - Bloom Beyond Borders',
            'pageTitle' => $product['name'],
            'description' => $product['summary'] ?: 'Shop ' . $product['name'] . ' from Bloom Beyond Borders.',
            'ogImage' => !empty($product['image_path']) ? '/' . ltrim($product['image_path'], '/') : null,
            'product' => $product,
            'csrfToken' => Csrf::token(),
            'notice' => $this->takeNotice(),
        ]);
    }

    private function takeNotice(): ?string
    {
        $notice = $_SESSION['shop_notice'] ?? null;
        unset($_SESSION['shop_notice']);

        return $notice;
    }
}
