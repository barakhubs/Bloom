<?php

namespace App\Core;

/** Base for /account pages: signed-out visitors are sent to the sign-in page and brought back afterwards. */
abstract class CustomerController extends Controller
{
    protected array $customer;

    public function __construct()
    {
        $customer = CustomerAuth::user();

        if ($customer === null) {
            $next = $_SERVER['REQUEST_METHOD'] === 'GET' ? (string) ($_SERVER['REQUEST_URI'] ?? '/account') : '/account';
            header('Location: /account/login?next=' . rawurlencode(CustomerAuth::safeNext($next)));
            exit;
        }

        $this->customer = $customer;
    }
}
