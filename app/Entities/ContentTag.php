<?php

namespace App\Entities;

use Core\Annotations\ORM\ORM;
use Core\Annotations\ORM\Id;
use Core\Annotations\ORM\AutoIncrement;
use Core\Annotations\ORM\Column;
use Core\Entities\AbstractEntity;

#[ORM(table: 'content_tags')]
class ContentTag extends AbstractEntity {

    #[Id]
    #[AutoIncrement]
    #[Column(type: 'int')]
    private int $id;

    #[Column(type: 'int', name: 'content_id')]
    private int $contentId;

    #[Column(type: 'int', name: 'tag_id')]
    private int $tagId;

    public function getId(): int { return $this->id; }

    public function getContentId(): int { return $this->contentId; }
    public function setContentId(int $contentId): self { $this->contentId = $contentId; return $this; }

    public function getTagId(): int { return $this->tagId; }
    public function setTagId(int $tagId): self { $this->tagId = $tagId; return $this; }
}