<?php

namespace App\Core;

use App\Models\AdminUserToken;

/**
 * Issues an invite / password-reset token for a back-office user and emails
 * the set-password link.
 */
class AccountLinks
{
    /**
     * @param array $user admin_users row (id, name, email)
     * @return array{url: string, sent: bool} the link, and whether the email went out -
     *         when it didn't (SMTP unset/broken) the admin UI shows the link to copy instead.
     */
    public static function send(array $user, string $purpose): array
    {
        $token = (new AdminUserToken())->issue((int) $user['id'], $purpose);
        $url = self::url($token);
        $sent = Mailer::sendAccountLink($user['email'], $user['name'] ?: $user['email'], $url, $purpose);

        return ['url' => $url, 'sent' => $sent];
    }

    private static function url(string $token): string
    {
        $appConfig = require dirname(__DIR__) . '/Config/app.php';

        return rtrim($appConfig['base_url'], '/') . '/admin/set-password/' . $token;
    }
}
