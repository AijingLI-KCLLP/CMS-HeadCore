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

    public function findAll(): array
    {
        return $this->raw('SELECT * FROM contents WHERE deleted_at IS NULL');
    }

    public function findBySlug(string $slug): ?Content
    {
        $results = $this->raw(
            'SELECT * FROM contents WHERE slug = :slug AND deleted_at IS NULL',
            ['slug' => $slug]
        );
        return $results[0] ?? null;
    }

    public function findByStatus(string $status): array
    {
        return $this->raw(
            'SELECT * FROM contents WHERE status = :status AND deleted_at IS NULL',
            ['status' => $status]
        );
    }

    public function findByAuthor(int $authorId): array
    {
        return $this->raw(
            'SELECT * FROM contents WHERE author_id = :author_id AND deleted_at IS NULL',
            ['author_id' => $authorId]
        );
    }

    public function findByCategory(int $categoryId): array
    {
        return $this->raw(
            'SELECT * FROM contents WHERE category_id = :category_id AND deleted_at IS NULL',
            ['category_id' => $categoryId]
        );
    }

    public function softDelete(Content $content): void
    {
        $content->setDeletedAt(date('Y-m-d H:i:s'));
        $this->update($content);
    }
}