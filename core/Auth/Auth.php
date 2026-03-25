<?php

namespace Core\Auth;

use Core\Entities\AbstractEntity;
use Core\Http\Session;

class Auth
{
    private const SESSION_KEY = 'auth_user_id';
    private const SESSION_ROLE = 'auth_user_role';

    public static function login(AbstractEntity $user, string $role): void
    {
        Session::start();
        session_regenerate_id(true);
        Session::set(self::SESSION_KEY, $user->getId());
        Session::set(self::SESSION_ROLE, $role);
    }

    public static function role(): ?string
    {
        return Session::get(self::SESSION_ROLE);
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
        Session::remove(self::SESSION_ROLE);
    }

    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function id(): int|string|null
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function guard(): void
    {
        if (!self::check()) {
            throw new \Exception('Unauthorized', 401);
        }
    }
}