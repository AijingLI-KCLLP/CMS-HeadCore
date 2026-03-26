<?php

namespace App\Services;

use App\Entities\Content;
use App\Repositories\ContentRepository;
use Core\Services\AbstractService;

class ContentService extends AbstractService
{
    public function __construct()
    {
        parent::__construct(new ContentRepository());
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

        $this->repository->save($content);
        return $content;
    }

    public function update(Content $content, array $fields, int $updatedBy): void
    {
        if (isset($fields['title'])) {
            $content->setTitle($fields['title']);
        }
        if (isset($fields['body'])) {
            $content->setBody($fields['body']);
        }
        if (isset($fields['category_id'])) {
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

    public function delete(Content $content): void
    {
        $this->repository->remove($content);
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