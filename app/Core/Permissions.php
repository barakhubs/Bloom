<?php

namespace App\Core;

/**
 * The fixed set of back-office permissions, one per admin section. Roles are
 * granted any subset of these slugs (role_permissions.permission); the system
 * Super Admin role implicitly has all of them.
 */
class Permissions
{
    public const ALL = [
        'settings' => 'Site Settings',
        'team' => 'Team',
        'partners' => 'Partners',
        'gallery' => 'Gallery',
        'blog' => 'Blog Posts',
        'comments' => 'Comment Moderation',
        'story' => 'Our Story',
        'contact' => 'Contact Submissions',
        'users' => 'Users & Roles',
    ];
}
