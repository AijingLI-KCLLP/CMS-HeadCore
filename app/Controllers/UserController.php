<?php

namespace App\Controllers;

use App\Services\UserService;
use Core\Auth\Acl;
use Core\Auth\Auth;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function process(Request $request): Response
    {
        $method = $request->getMethod();
        $path   = $request->getPath();
        $id     = $request->getSlug('id');

        return match (true) {
            $method === 'GET'    && $path === '/admin/users'  => $this->handleAdminList(),
            $method === 'POST'   && $id !== null              => $this->handleChangeRole($request),
            $method === 'DELETE' && $id !== null              => $this->handleDelete($request),
            $method === 'GET'    && $id !== null              => $this->handleGetOne($request),
            $method === 'GET'                                 => $this->handleList(),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function handleAdminList(): Response
    {
        if (!Acl::can(Auth::role(), 'user.manage')) {
            return Response::error('Forbidden', 403);
        }

        $users = array_map(fn($u) => $this->safeArray($u), $this->userService->listUsers());
        return Response::json($users);
    }

    private function handleChangeRole(Request $request): Response
    {
        if (!Acl::can(Auth::role(), 'user.manage')) {
            return Response::error('Forbidden', 403);
        }

        $body = $request->getJsonBody();
        if (empty($body['role'])) {
            return Response::error('Missing role', 422);
        }

        try {
            $this->userService->changeRole(
                (int) $request->getSlug('id'),
                $body['role'],
                (int) Auth::id()
            );
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json(['message' => 'Role updated']);
    }

    private function handleDelete(Request $request): Response
    {
        if (!Acl::can(Auth::role(), 'user.manage')) {
            return Response::error('Forbidden', 403);
        }

        try {
            $this->userService->deleteUser(
                (int) $request->getSlug('id'),
                (int) Auth::id()
            );
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json(['message' => 'User deleted']);
    }

    private function handleGetOne(Request $request): Response
    {
        $user = $this->userService->getUserById((int) $request->getSlug('id'));
        if ($user === null) {
            return Response::error('User not found', 404);
        }
        return Response::json($this->safeArray($user));
    }

    private function handleList(): Response
    {
        $users = array_map(fn($u) => $this->safeArray($u), $this->userService->listUsers());
        return Response::json($users);
    }

    private function safeArray(\App\Entities\User $user): array
    {
        return [
            'id'         => $user->getId(),
            'name'       => $user->getName(),
            'email'      => $user->getEmail(),
            'role'       => $user->getRole(),
            'status'     => $user->getStatus(),
            'created_at' => $user->getCreatedAt(),
        ];
    }
}