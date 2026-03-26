<?php

namespace App\Services;

use App\Entities\Tag;
use App\Repositories\ContentRepository;
use App\Repositories\ContentTagRepository;
use App\Repositories\TagRepository;
use Core\Services\AbstractService;

class TagService extends AbstractService
{
    private ContentTagRepository $contentTagRepository;
    private ContentRepository    $contentRepository;

    public function __construct()
    {
        parent::__construct(new TagRepository());
        $this->contentTagRepository = new ContentTagRepository();
        $this->contentRepository    = new ContentRepository();
    }

    public function listAll(): array
    {
        return $this->repository->findAll();
    }

    public function getById(int $id): ?Tag
    {
        return $this->repository->find($id);
    }

    public function getBySlug(string $slug): ?Tag
    {
        return $this->repository->findBySlug($slug);
    }

    public function create(string $name): Tag
    {
        $slug = $this->generateUniqueSlug($name);

        $tag = (new Tag())
            ->setName($name)
            ->setSlug($slug);

        $insertedId = $this->repository->save($tag);
        return $this->repository->find((int) $insertedId) ?? $tag;
    }

    public function delete(Tag $tag): void
    {
        $contentTags = $this->contentTagRepository->findByTag($tag->getId());

        foreach ($contentTags as $ct) {
            $content = $this->contentRepository->find($ct->getContentId());
            if ($content !== null && $content->getStatus() === 'published') {
                throw new \RuntimeException(
                    "Cannot delete tag '{$tag->getName()}': it is used by published contents.", 409
                );
            }
        }

        $this->repository->remove($tag);
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $slug = $base;
        $i    = 2;

        while (true) {
            $existing = $this->repository->findBySlug($slug);
            if ($existing === null || $existing->getId() === $excludeId) {
                break;
            }
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}