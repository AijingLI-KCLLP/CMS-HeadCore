<?php

namespace App\Repositories;

use App\Entities\Content;
use Core\Repositories\AbstractRepository;

class ContentRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Content::class);
    }

    public function findBySlug(string $slug): ?Content
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findByAuthor(int $authorId): array
    {
        return $this->findBy(['author_id' => $authorId]);
    }

    public function findByCategory(int $categoryId): array
    {
        return $this->findBy(['category_id' => $categoryId]);
    }
}