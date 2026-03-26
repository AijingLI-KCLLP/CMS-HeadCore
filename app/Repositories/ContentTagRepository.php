<?php

namespace App\Repositories;

use App\Entities\ContentTag;
use Core\Repositories\AbstractRepository;

class ContentTagRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(ContentTag::class);
    }

    public function findByContent(int $contentId): array
    {
        return $this->findBy(['content_id' => $contentId]);
    }

    public function findByTag(int $tagId): array
    {
        return $this->findBy(['tag_id' => $tagId]);
    }
}
