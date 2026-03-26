<?php

namespace App\Repositories;

use App\Entities\Category;
use Core\Repositories\AbstractRepository;

class CategoryRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Category::class);
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
