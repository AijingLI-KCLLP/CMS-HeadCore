<?php

namespace Core\Auth;

use Core\Config\Config;

class Acl
{
    public static function can(string $role, string $permission): bool
    {
        $permissions = Config::get('auth.permissions');
        return in_array($permission, $permissions[$role] ?? [], true);
    }
}