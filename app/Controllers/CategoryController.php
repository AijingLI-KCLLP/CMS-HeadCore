<?php

namespace App\Controllers;

use App\Services\CategoryService;
use Core\Auth\Acl;
use Core\Auth\Auth;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class CategoryController extends AbstractController
{
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->categoryService = new CategoryService();
    }

    public function process(Request $request): Response
    {
        $method = $request->getMethod();
        $id     = $request->getSlug('id');

        return match (true) {
            $method === 'GET'    && $id === null  => $this->handleList(),
            $method === 'GET'    && $id !== null  => $this->handleGetOne((int) $id),
            $method === 'POST'   && $id === null  => $this->handleCreate($request),
            $method === 'PUT'    && $id !== null  => $this->handleUpdate($request, (int) $id),
            $method === 'DELETE' && $id !== null  => $this->handleDelete((int) $id),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function handleList(): Response
    {
        $categories = array_map(fn($c) => $this->toArray($c), $this->categoryService->listAll());
        return Response::json($categories);
    }

    private function handleGetOne(int $id): Response
    {
        $category = $this->categoryService->getById($id);
        if ($category === null) {
            return Response::error('Category not found', 404);
        }
        return Response::json($this->toArray($category));
    }

    private function handleCreate(Request $request): Response
    {
        if (!Acl::can(Auth::role(), 'taxonomy.manage')) {
            return Response::error('Forbidden', 403);
        }

        $body = $request->getJsonBody();
        if (empty($body['name'])) {
            return Response::error('Missing name', 422);
        }

        try {
            $category = $this->categoryService->create($body['name']);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 422);
        }

        return Response::json($this->toArray($category), 201);
    }

    private function handleUpdate(Request $request, int $id): Response
    {
        if (!Acl::can(Auth::role(), 'taxonomy.manage')) {
            return Response::error('Forbidden', 403);
        }

        $category = $this->categoryService->getById($id);
        if ($category === null) {
            return Response::error('Category not found', 404);
        }

        $body = $request->getJsonBody();
        if (empty($body['name'])) {
            return Response::error('Missing name', 422);
        }

        try {
            $this->categoryService->update($category, $body['name']);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json($this->toArray($category));
    }

    private function handleDelete(int $id): Response
    {
        if (!Acl::can(Auth::role(), 'taxonomy.manage')) {
            return Response::error('Forbidden', 403);
        }

        $category = $this->categoryService->getById($id);
        if ($category === null) {
            return Response::error('Category not found', 404);
        }

        try {
            $this->categoryService->delete($category);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json(['message' => 'Category deleted']);
    }

    private function toArray(\App\Entities\Category $category): array
    {
        return [
            'id'   => $category->getId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
        ];
    }
}