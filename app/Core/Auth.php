<?php

namespace App\Core;

use App\Models\AdminUser;
use App\Models\Role;

class Auth
{
    /** Per-request cache of the logged-in user row (false = looked up, none). */
    private static array|false|null $user = null;

    /**
     * Logged in AND the account still exists and is active - so disabling or
     * deleting a user ends their existing session on their next request.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['admin_user_id']) ? (int) $_SESSION['admin_user_id'] : null;
    }

    /**
     * The current user's row (joined with role_name / is_system) plus a
     * 'permissions' list, re-read from the DB once per request so role and
     * status changes apply immediately.
     */
    public static function user(): ?array
    {
        if (self::$user === null) {
            self::$user = false;
            $id = self::id();
            $row = $id !== null ? (new AdminUser())->findById($id) : null;

            if ($row !== null && $row['status'] === 'active') {
                $row['permissions'] = (new Role())->permissions((int) $row['role_id']);
                self::$user = $row;
            } elseif ($id !== null) {
                unset($_SESSION['admin_user_id']);
            }
        }

        return self::$user === false ? null : self::$user;
    }

    public static function isSuperAdmin(): bool
    {
        return !empty(self::user()['is_system']);
    }

    public static function can(string $permission): bool
    {
        $user = self::user();

        if ($user === null) {
            return false;
        }

        return !empty($user['is_system']) || in_array($permission, $user['permissions'], true);
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = $userId;
        self::$user = null;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_user_id']);
        session_regenerate_id(true);
        self::$user = null;
    }
}
