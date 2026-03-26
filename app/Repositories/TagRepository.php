<?php

namespace App\Repositories;

use App\Entities\Tag;
use Core\Repositories\AbstractRepository;

class TagRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Tag::class);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}