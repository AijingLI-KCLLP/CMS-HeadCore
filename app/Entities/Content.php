<?php

namespace App\Entities;

use Core\Annotations\ORM\ORM;
use Core\Annotations\ORM\Id;
use Core\Annotations\ORM\AutoIncrement;
use Core\Annotations\ORM\Column;
use Core\Entities\AbstractEntity;

#[ORM(table: 'contents')]
class Content extends AbstractEntity {

    #[Id]
    #[AutoIncrement]
    #[Column(type: 'int')]
    private int $id;

    #[Column(type: 'VARCHAR', size: 255)]
    private string $title;

    #[Column(type: 'VARCHAR', size: 255, unique: true)]
    private string $slug;

    #[Column(type: 'TEXT')]
    private string $body;

    #[Column(type: 'int', name: 'author_id')]
    private int $authorId;

    #[Column(type: 'VARCHAR', size: 20, enum: ['draft', 'review', 'published', 'archived'])]
    private string $status;

    #[Column(type: 'int', name: 'category_id', nullable: true)]
    private ?int $categoryId = null;

    #[Column(type: 'int', name: 'updated_by', nullable: true)]
    private ?int $updatedBy = null;

    #[Column(type: 'TIMESTAMP', name: 'published_at', nullable: true)]
    private ?string $publishedAt = null;

    #[Column(type: 'TIMESTAMP', name: 'created_at')]
    private string $createdAt;

    #[Column(type: 'TIMESTAMP', name: 'updated_at', nullable: true)]
    private ?string $updatedAt = null;

    #[Column(type: 'TIMESTAMP', name: 'deleted_at', nullable: true)]
    private ?string $deletedAt = null;

    public function getId(): int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getBody(): string { return $this->body; }
    public function setBody(string $body): self { $this->body = $body; return $this; }

    public function getAuthorId(): int { return $this->authorId; }
    public function setAuthorId(int $authorId): self { $this->authorId = $authorId; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getCategoryId(): ?int { return $this->categoryId; }
    public function setCategoryId(?int $categoryId): self { $this->categoryId = $categoryId; return $this; }

    public function getUpdatedBy(): ?int { return $this->updatedBy; }
    public function setUpdatedBy(?int $updatedBy): self { $this->updatedBy = $updatedBy; return $this; }

    public function getPublishedAt(): ?string { return $this->publishedAt; }
    public function setPublishedAt(?string $publishedAt): self { $this->publishedAt = $publishedAt; return $this; }

    public function getCreatedAt(): string { return $this->createdAt; }
    public function setCreatedAt(string $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function setUpdatedAt(?string $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function getDeletedAt(): ?string { return $this->deletedAt; }
    public function setDeletedAt(?string $deletedAt): self { $this->deletedAt = $deletedAt; return $this; }
}