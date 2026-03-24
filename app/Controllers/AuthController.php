<?php

namespace App\Controllers;

use App\Services\UserService;
use Core\Auth\Auth;
use Core\Auth\PasswordHasher;
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
            $request->getMethod() === 'POST' => $this->handleLogin($request),
            $request->getMethod() === 'DELETE' => $this->handleDelete(),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function handleLogin(Request $request): Response
    {
        $body = $request->getJsonBody();

        if (empty($body['email']) || empty($body['password'])) {
            return Response::error('Missing email or password', 422);
        }

        $user = $this->userService->getUserByEmail($body['email']);

        if ($user === null || !PasswordHasher::verify($body['password'], $user->getPasswordHash())) {
            return Response::error('Invalid credentials', 401);
        }

        Auth::login($user);

        return Response::json(['message' => 'Logged in', 'user_id' => Auth::id()]);
    }

    private function handleSignUp(Request $request): Response
    {
        $body = $request->getJsonBody();

        if (empty($body['email']) || empty($body['password'])) {
            return Response::error('Missing email or password', 422);
        }

        try {
            $this->userService->signUp($body['email'], $body['password']);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), $e->getCode());
        }

        return Response::json(['message' => 'Account created'], 201);
    }

    private function handleDelete(): Response
    {
        Auth::logout();
        return Response::json(['message' => 'Logged out']);
    }
}
