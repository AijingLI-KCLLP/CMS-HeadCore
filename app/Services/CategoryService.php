<?php

namespace App\Services;

use App\Entities\Category;
use App\Repositories\CategoryRepository;
use App\Repositories\ContentRepository;
use Core\Services\AbstractService;

class CategoryService extends AbstractService
{
    private ContentRepository $contentRepository;

    public function __construct()
    {
        parent::__construct(new CategoryRepository());
        $this->contentRepository = new ContentRepository();
    }

    public function listAll(): array
    {
        return $this->repository->findAll();
    }

    public function getById(int $id): ?Category
    {
        return $this->repository->find($id);
    }

    public function getBySlug(string $slug): ?Category
    {
        return $this->repository->findBySlug($slug);
    }

    public function create(string $name): Category
    {
        $slug = $this->generateUniqueSlug($name);

        $category = (new Category())
            ->setName($name)
            ->setSlug($slug);

        $insertedId = $this->repository->save($category);
        return $this->repository->find((int) $insertedId) ?? $category;
    }

    public function update(Category $category, string $name): void
    {
        $slug = $this->generateUniqueSlug($name, $category->getId());

        $category->setName($name)->setSlug($slug);
        $this->repository->update($category);
    }

    public function delete(Category $category): void
    {
        $contents = $this->contentRepository->findByCategory($category->getId());

        foreach ($contents as $content) {
            if ($content->getStatus() === 'published') {
                throw new \RuntimeException(
                    "Cannot delete category '{$category->getName()}': it has published contents.", 409
                );
            }
        }

        foreach ($contents as $content) {
            $content->setCategoryId(null);
            $this->contentRepository->update($content);
        }

        $this->repository->remove($category);
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
