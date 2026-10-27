<?php

namespace App\Core;

use App\Models\CustomerToken;

/** Issues a verify / reset token for a customer account and emails the link. */
class CustomerLinks
{
    /** @param array $customer customers row (id, name, email). Returns whether the email went out. */
    public static function send(array $customer, string $purpose): bool
    {
        $token = (new CustomerToken())->issue((int) $customer['id'], $purpose);
        $path = $purpose === 'verify' ? '/account/verify/' : '/account/reset/';

        return Mailer::sendCustomerLink($customer['email'], $customer['name'], self::url($path . $token), $purpose);
    }

    private static function url(string $path): string
    {
        $appConfig = require dirname(__DIR__) . '/Config/app.php';

        return rtrim($appConfig['base_url'], '/') . $path;
    }
}
