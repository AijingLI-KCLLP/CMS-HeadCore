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
            $request->getMethod() === 'GET'  && $request->getPath() === '/login'  => $this->showLogin(),
            $request->getMethod() === 'POST' && $request->getPath() === '/signup' => $this->handleSignUp($request),
            $request->getMethod() === 'POST' && $request->getPath() === '/login'  => $this->handleLogin($request),
            $request->getMethod() === 'POST' && $request->getPath() === '/logout' => $this->handleLogout(),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function showLogin(string $error = '', string $prefillEmail = ''): Response
    {
        ob_start();
        $error       = $error;
        $prefillEmail = $prefillEmail;
        require __DIR__ . '/../../resources/views/auth/login.php';
        $html = ob_get_clean();

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    private function handleLogin(Request $request): Response
    {
        $body = $request->expectsJson()
            ? $request->getJsonBody()
            : $request->getFormBody();

        if (empty($body['email']) || empty($body['password'])) {
            if ($request->expectsJson()) {
                return Response::error('Missing email or password', 422);
            }
            return $this->showLogin('Email et mot de passe requis.', $body['email'] ?? '');
        }

        try {
            $user = $this->userService->login($body['email'], $body['password']);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
            }
            return $this->showLogin($e->getMessage(), $body['email']);
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
            $user = $this->userService->signUp($body['email'], $body['password'], $body['name'] ?? null);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json(['message' => 'Account created', 'id' => $user->getId(), 'email' => $user->getEmail()], 201);
    }

    private function handleLogout(): Response
    {
        Auth::logout();
        return Response::redirect('/login');
    }
}
