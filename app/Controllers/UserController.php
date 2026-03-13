<?php

namespace App\Controllers;

use App\Services\UserService;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

/**
 * Thin controller: orchestration only.
 * - No SQL, no PDO, no business logic here.
 * - Delegates everything to UserService.
 */
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function process(Request $request): Response
    {
        return match ($request->getMethod()) {
            'GET'  => $this->handleGet($request),
            'POST' => $this->handlePost($request),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function handleGet(Request $request): Response
    {
        $id = $request->getSlug('id');

        if ($id !== null) {
            $user = $this->userService->getUserById((int) $id);
            if ($user === null) {
                return Response::error('User not found', 404);
            }
            return Response::json($user->toArray());
        }

        $users = array_map(fn($u) => $u->toArray(), $this->userService->listUsers());
        return Response::json($users);
    }

    private function handlePost(Request $request): Response
    {
        $body = $request->getJsonBody();

        foreach (['name', 'email', 'password'] as $field) {
            if (empty($body[$field])) {
                return Response::error("Missing field: $field", 422);
            }
        }

        $id = $this->userService->createUser($body['name'], $body['email'], $body['password']);
        return Response::json(['id' => $id], 201);
    }
}