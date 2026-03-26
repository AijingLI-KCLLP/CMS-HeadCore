<?php

namespace App\Controllers;

use App\Services\ContentService;
use App\Services\WorkflowService;
use Core\Auth\Acl;
use Core\Auth\Auth;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class ContentController extends AbstractController
{
    private ContentService  $contentService;
    private WorkflowService $workflowService;

    public function __construct()
    {
        $this->contentService  = new ContentService();
        $this->workflowService = new WorkflowService();
    }

    public function process(Request $request): Response
    {
        $method = $request->getMethod();
        $path   = $request->getPath();
        $id     = $request->getSlug('id');

        return match (true) {
            $method === 'GET'    && $id === null                      => $this->handleList(),
            $method === 'GET'    && $id !== null                      => $this->handleGetOne((int) $id),
            $method === 'POST'   && $id === null                      => $this->handleCreate($request),
            $method === 'PUT'    && $id !== null                      => $this->handleUpdate($request, (int) $id),
            $method === 'POST'   && str_ends_with($path, '/status')   => $this->handleTransition($request, (int) $id),
            $method === 'POST'   && str_ends_with($path, '/tags')     => $this->handleAttachTag($request, (int) $id),
            $method === 'DELETE' && $request->getSlug('tagId') !== null => $this->handleDetachTag((int) $id, (int) $request->getSlug('tagId')),
            $method === 'DELETE' && $id !== null                      => $this->handleDelete($request, (int) $id),
            default => Response::error('Method not allowed', 405),
        };
    }

    private function handleList(): Response
    {
        if (!Acl::can(Auth::role(), 'content.read')) {
            return Response::error('Forbidden', 403);
        }

        $contents = array_map(fn($c) => $this->toArray($c), $this->contentService->listAll());
        return Response::json($contents);
    }

    private function handleGetOne(int $id): Response
    {
        if (!Acl::can(Auth::role(), 'content.read')) {
            return Response::error('Forbidden', 403);
        }

        $content = $this->contentService->getById($id);
        if ($content === null) {
            return Response::error('Content not found', 404);
        }

        return Response::json($this->toArray($content));
    }

    private function handleCreate(Request $request): Response
    {
        if (!Acl::can(Auth::role(), 'content.create')) {
            return Response::error('Forbidden', 403);
        }

        $body = $request->getJsonBody();

        try {
            $content = $this->contentService->create(
                $body['title']       ?? '',
                $body['body']        ?? '',
                (int) Auth::id(),
                isset($body['category_id']) ? (int) $body['category_id'] : null
            );
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 422);
        }

        return Response::json($this->toArray($content), 201);
    }

    private function handleUpdate(Request $request, int $id): Response
    {
        $content = $this->contentService->getById($id);
        if ($content === null) {
            return Response::error('Content not found', 404);
        }

        $isOwn      = $content->getAuthorId() === (int) Auth::id();
        $permission = $isOwn ? 'content.edit.own' : 'content.edit.any';

        if (!Acl::can(Auth::role(), $permission)) {
            return Response::error('Forbidden', 403);
        }

        $body = $request->getJsonBody();

        try {
            $this->contentService->update($content, $body, (int) Auth::id());
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json($this->toArray($content));
    }

    private function handleTransition(Request $request, int $id): Response
    {
        $content = $this->contentService->getById($id);
        if ($content === null) {
            return Response::error('Content not found', 404);
        }

        $body = $request->getJsonBody();
        if (empty($body['status'])) {
            return Response::error('Missing status', 422);
        }

        try {
            $this->workflowService->transition(
                $content,
                $body['status'],
                Auth::role() ?? '',
                (int) Auth::id()
            );
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), (int) $e->getCode() ?: 500);
        }

        return Response::json($this->toArray($content));
    }

    private function handleDelete(Request $request, int $id): Response
    {
        $content = $this->contentService->getById($id);
        if ($content === null) {
            return Response::error('Content not found', 404);
        }

        if (Acl::can(Auth::role(), 'content.delete')) {
            $this->contentService->hardDelete($content);
            return Response::json(['message' => 'Content permanently deleted']);
        }

        $isOwn = $content->getAuthorId() === (int) Auth::id();
        if ($isOwn && Acl::can(Auth::role(), 'content.edit.own')) {
            $this->contentService->softDelete($content);
            return Response::json(['message' => 'Content deleted']);
        }

        return Response::error('Forbidden', 403);
    }

    private function handleAttachTag(Request $request, int $contentId): Response
    {
        $content = $this->contentService->getById($contentId);
        if ($content === null) {
            return Response::error('Content not found', 404);
        }

        $isOwn = $content->getAuthorId() === (int) Auth::id();
        if (!$isOwn && !Acl::can(Auth::role(), 'content.edit.any')) {
            return Response::error('Forbidden', 403);
        }

        $body = $request->getJsonBody();
        if (empty($body['tag_id'])) {
            return Response::error('Missing tag_id', 422);
        }

        $this->contentService->attachTag($contentId, (int) $body['tag_id']);
        return Response::json(['message' => 'Tag attached']);
    }

    private function handleDetachTag(int $contentId, int $tagId): Response
    {
        $content = $this->contentService->getById($contentId);
        if ($content === null) {
            return Response::error('Content not found', 404);
        }

        $isOwn = $content->getAuthorId() === (int) Auth::id();
        if (!$isOwn && !Acl::can(Auth::role(), 'content.edit.any')) {
            return Response::error('Forbidden', 403);
        }

        $this->contentService->detachTag($contentId, $tagId);
        return Response::json(['message' => 'Tag detached']);
    }

    private function toArray(\App\Entities\Content $content): array
    {
        return [
            'id'           => $content->getId(),
            'title'        => $content->getTitle(),
            'slug'         => $content->getSlug(),
            'body'         => $content->getBody(),
            'status'       => $content->getStatus(),
            'author_id'    => $content->getAuthorId(),
            'category_id'  => $content->getCategoryId(),
            'published_at' => $content->getPublishedAt(),
            'created_at'   => $content->getCreatedAt(),
            'updated_at'   => $content->getUpdatedAt(),
        ];
    }
}