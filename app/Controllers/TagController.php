<?php

namespace App\Controllers;

use App\Services\TagService;
use Core\Auth\Acl;
use Core\Auth\Auth;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class TagController extends AbstractController
{
    private TagService $tagService;

    public function __construct()
    {
        $this->tagService = new TagService();
    }

    public function process(Request $request): Response
    {
        $method = $request->getMethod();
        $id     = $request->getSlug('id');

        return match (true) {
            $method === 'GET'    && $id === null  => $this->handleList(),
            $method === 'GET'    && $id !== null  => $this->handleGetOne((int) $id),
            $method === 'POST'   && $id === null  => $this->handleCreate($request),
            $method === 'DELETE' && $id !== null  => $this->handleDelete((int) $id),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function handleList(): Response
    {
        $tags = array_map(fn($t) => $this->toArray($t), $this->tagService->listAll());
        return Response::json($tags);
    }

    private function handleGetOne(int $id): Response
    {
        $tag = $this->tagService->getById($id);
        if ($tag === null) {
            return Response::error('Tag not found', 404);
        }
        return Response::json($this->toArray($tag));
    }

    private function handleCreate(Request $request): Response
    {
        if (!Acl::can(Auth::role(), 'tag.create')) {
            return Response::error('Forbidden', 403);
        }

        $body = $request->getJsonBody();
        if (empty($body['name'])) {
            return Response::error('Missing name', 422);
        }

        try {
            $tag = $this->tagService->create($body['name']);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 422);
        }

        return Response::json($this->toArray($tag), 201);
    }

    private function handleDelete(int $id): Response
    {
        if (!Acl::can(Auth::role(), 'taxonomy.manage')) {
            return Response::error('Forbidden', 403);
        }

        $tag = $this->tagService->getById($id);
        if ($tag === null) {
            return Response::error('Tag not found', 404);
        }

        try {
            $this->tagService->delete($tag);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json(['message' => 'Tag deleted']);
    }

    private function toArray(\App\Entities\Tag $tag): array
    {
        return [
            'id'   => $tag->getId(),
            'name' => $tag->getName(),
            'slug' => $tag->getSlug(),
        ];
    }
}