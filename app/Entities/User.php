<?php

namespace App\Entities;

use Core\Annotations\ORM\ORM;
use Core\Annotations\ORM\Id;
use Core\Annotations\ORM\AutoIncrement;
use Core\Annotations\ORM\Column;
use Core\Entities\AbstractEntity;

#[ORM(table: 'users')]
class User extends AbstractEntity {

    #[Id]
    #[AutoIncrement]
    #[Column(type: 'int')]
    private int $id;

    #[Column(type: 'VARCHAR', size: 255, nullable: true)]
    private ?string $name = null;

    #[Column(type: 'VARCHAR', size: 255, unique: true)]
    private string $email;

    #[Column(type: 'VARCHAR', size: 255, name: 'password_hash')]
    private string $passwordHash;

    #[Column(type: 'VARCHAR', size: 20, enum: ['admin', 'editor', 'author', 'reader'])]
    private string $role;

    #[Column(type: 'VARCHAR', size: 10, enum: ['active', 'deleted'])]
    private string $status;

    #[Column(type: 'TIMESTAMP', name: 'created_at')]
    private string $createdAt;

    #[Column(type: 'TIMESTAMP', name: 'updated_at', nullable: true)]
    private ?string $updatedAt = null;

    public function getId(): int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }

    public function getPasswordHash(): string {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): self {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function setRole(string $role): self {
        $this->role = $role;
        return $this;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): self {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): self {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}