<?php

namespace App\Services;

use App\Entities\Content;
use App\Entities\ContentTag;
use App\Repositories\ContentRepository;
use App\Repositories\ContentTagRepository;
use Core\Services\AbstractService;

class ContentService extends AbstractService
{
    private ContentTagRepository $contentTagRepository;

    public function __construct()
    {
        parent::__construct(new ContentRepository());
        $this->contentTagRepository = new ContentTagRepository();
    }

    public function listAll(): array
    {
        return $this->repository->findAll();
    }

    public function listPublished(): array
    {
        return $this->repository->findByStatus('published');
    }

    public function getById(int $id): ?Content
    {
        return $this->repository->find($id);
    }

    public function getBySlug(string $slug): ?Content
    {
        return $this->repository->findBySlug($slug);
    }

    public function create(string $title, string $body, int $authorId, ?int $categoryId = null): Content
    {
        if (empty(trim($title))) {
            throw new \RuntimeException('Title is required.', 422);
        }
        if (empty(trim($body))) {
            throw new \RuntimeException('Body is required.', 422);
        }

        $now  = date('Y-m-d H:i:s');
        $slug = $this->generateSlug($title, $now);

        $content = (new Content())
            ->setTitle($title)
            ->setSlug($slug)
            ->setBody($body)
            ->setAuthorId($authorId)
            ->setStatus('draft')
            ->setCategoryId($categoryId)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $insertedId = $this->repository->save($content);
        return $this->repository->find((int) $insertedId) ?? $content;
    }

    public function update(Content $content, array $fields, int $updatedBy): void
    {
        if (isset($fields['title']) && $fields['title'] !== $content->getTitle()) {
            $content->setTitle($fields['title']);
            $content->setSlug($this->generateSlug($fields['title'], $content->getCreatedAt()));
        }
        if (isset($fields['body'])) {
            $content->setBody($fields['body']);
        }
        if (array_key_exists('category_id', $fields)) {
            $content->setCategoryId($fields['category_id']);
        }

        $content->setUpdatedBy($updatedBy)
                ->setUpdatedAt(date('Y-m-d H:i:s'));

        $this->repository->update($content);
    }

    public function transition(Content $content, string $newStatus, int $updatedBy): void
    {
        $allowed = $this->allowedTransitions($content->getStatus());

        if (!in_array($newStatus, $allowed, true)) {
            throw new \RuntimeException(
                "Cannot transition from '{$content->getStatus()}' to '$newStatus'.", 422
            );
        }

        $content->setStatus($newStatus)
                ->setUpdatedBy($updatedBy)
                ->setUpdatedAt(date('Y-m-d H:i:s'));

        if ($newStatus === 'published') {
            $content->setPublishedAt(date('Y-m-d H:i:s'));
        }

        $this->repository->update($content);
    }

    public function softDelete(Content $content): void
    {
        $this->repository->softDelete($content);
    }

    public function hardDelete(Content $content): void
    {
        $this->repository->remove($content);
    }

    public function setCategory(Content $content, ?int $categoryId, int $updatedBy): void
    {
        $content->setCategoryId($categoryId)
                ->setUpdatedBy($updatedBy)
                ->setUpdatedAt(date('Y-m-d H:i:s'));

        $this->repository->update($content);
    }

    public function attachTag(int $contentId, int $tagId): void
    {
        $existing = $this->contentTagRepository->findByContent($contentId);
        foreach ($existing as $ct) {
            if ($ct->getTagId() === $tagId) {
                return;
            }
        }

        $ct = (new ContentTag())->setContentId($contentId)->setTagId($tagId);
        $this->contentTagRepository->save($ct);
    }

    public function detachTag(int $contentId, int $tagId): void
    {
        $existing = $this->contentTagRepository->findByContent($contentId);
        foreach ($existing as $ct) {
            if ($ct->getTagId() === $tagId) {
                $this->contentTagRepository->remove($ct);
                return;
            }
        }
    }

    public function getTagIds(int $contentId): array
    {
        return array_map(
            fn($ct) => $ct->getTagId(),
            $this->contentTagRepository->findByContent($contentId)
        );
    }

    private function generateSlug(string $title, string $createdAt): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        $hash = substr(md5($title . $createdAt), 0, 6);
        return $base . '-' . $hash;
    }

    private function allowedTransitions(string $current): array
    {
        return match ($current) {
            'draft'     => ['review'],
            'review'    => ['draft', 'published'],
            'published' => ['archived'],
            'archived'  => [],
            default     => [],
        };
    }
}