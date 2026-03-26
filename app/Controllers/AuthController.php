<?php

namespace App\Controllers;

use App\Services\UserService;
use Core\Auth\Auth;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class AuthController extends AbstractController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function process(Request $request): Response
    {
        return match (true) {
            $request->getMethod() === 'POST' && $request->getPath() === '/signup' => $this->handleSignUp($request),
            $request->getMethod() === 'POST' && $request->getPath() === '/login'  => $this->handleLogin($request),
            $request->getMethod() === 'POST' && $request->getPath() === '/logout' => $this->handleLogout(),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function handleLogin(Request $request): Response
    {
        $body = $request->getJsonBody();

        if (empty($body['email']) || empty($body['password'])) {
            return Response::error('Missing email or password', 422);
        }

        try {
            $user = $this->userService->login($body['email'], $body['password']);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        Auth::login($user, $user->getRole());

        return Response::redirect('/admin');
    }

    private function handleSignUp(Request $request): Response
    {
        $body = $request->getJsonBody();

        if (empty($body['email']) || empty($body['password'])) {
            return Response::error('Missing email or password', 422);
        }

        try {
            $this->userService->signUp($body['email'], $body['password'], $body['name'] ?? null);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json(['message' => 'Account created'], 201);
    }

    private function handleLogout(): Response
    {
        Auth::logout();
        return Response::redirect('/login');
    }
}
