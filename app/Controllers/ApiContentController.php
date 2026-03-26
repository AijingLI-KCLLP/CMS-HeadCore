<?php

namespace App\Controllers;

use App\Services\ContentService;
use Core\Http\Request;
use Core\Http\Response;

class ApiContentController extends ApiController
{
    private ContentService $contentService;

    public function __construct()
    {
        $this->contentService = new ContentService();
    }

    public function process(Request $request): Response
    {
        $id = $request->getSlug('id');

        return match (true) {
            $id === null  => $this->handleList(),
            $id !== null  => $this->handleGetOne($id),
            default       => $this->error('Method not allowed', 405),
        };
    }

    private function handleList(): Response
    {
        $contents = array_map(
            fn($c) => $this->toArray($c),
            $this->contentService->listPublished()
        );

        return $this->success($contents);
    }

    private function handleGetOne(string $slug): Response
    {
        $content = $this->contentService->getBySlug($slug);

        if ($content === null) {
            return $this->error('Content not found', 404);
        }

        return $this->success($this->toArray($content, true));
    }

    private function toArray(\App\Entities\Content $content, bool $withBody = false): array
    {
        $data = [
            'id'           => $content->getId(),
            'title'        => $content->getTitle(),
            'slug'         => $content->getSlug(),
            'author_id'    => $content->getAuthorId(),
            'category_id'  => $content->getCategoryId(),
            'published_at' => $content->getPublishedAt(),
        ];

        if ($withBody) {
            $data['body'] = $content->getBody();
        }

        return $data;
    }
}
