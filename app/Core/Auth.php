<?php

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['admin_user_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['admin_user_id']) ? (int) $_SESSION['admin_user_id'] : null;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = $userId;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_user_id']);
        session_regenerate_id(true);
    }
}
