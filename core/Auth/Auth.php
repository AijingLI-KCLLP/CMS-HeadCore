<?php

namespace Core\Auth;

use Core\Entities\AbstractEntity;
use Core\Http\Session;

class Auth
{
    private const SESSION_KEY = 'auth_user_id';

    /**
     * Store the authenticated user's ID in session.
     */
    public static function login(AbstractEntity $user): void
    {
        Session::set(self::SESSION_KEY, $user->getId());
    }

    /**
     * Clear the authenticated user from session.
     */
    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
    }

    /**
     * Check if a user is currently authenticated.
     */
    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    /**
     * Get the current authenticated user's ID.
     * Returns null if not authenticated.
     */
    public static function id(): int|string|null
    {
        return Session::get(self::SESSION_KEY);
    }

    /**
     * Protect a route: throws 401 if not authenticated.
     * Call at the top of any controller method that requires auth.
     */
    public static function guard(): void
    {
        if (!self::check()) {
            throw new \Exception('Unauthorized', 401);
        }
    }
}