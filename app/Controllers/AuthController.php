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
        return match ($request->getMethod()) {
            'POST' => $this->handlePost($request),
            'DELETE' => $this->handleDelete(),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function handlePost(Request $request): Response
    {
        $body = $request->getJsonBody();

        if (empty($body['email']) || empty($body['password'])) {
            return Response::error('Missing email or password', 422);
        }

        $user = $this->userService->getUserByEmail($body['email']);

        if ($user === null || !PasswordHasher::verify($body['password'], $user->getPassword())) {
            return Response::error('Invalid credentials', 401);
        }

        Auth::login($user);

        return Response::json(['message' => 'Logged in', 'user_id' => Auth::id()]);
    }

    private function handleDelete(): Response
    {
        Auth::logout();
        return Response::json(['message' => 'Logged out']);
    }
}
