<?php

namespace App\Entities;

use Core\Annotations\ORM\ORM;
use Core\Annotations\ORM\Id;
use Core\Annotations\ORM\AutoIncrement;
use Core\Annotations\ORM\Column;
use Core\Entities\AbstractEntity;

#[ORM(table: 'categories')]
class Category extends AbstractEntity {

    #[Id]
    #[AutoIncrement]
    #[Column(type: 'int')]
    private int $id;

    #[Column(type: 'VARCHAR', size: 100, unique: true)]
    private string $name;

    #[Column(type: 'VARCHAR', size: 100, unique: true)]
    private string $slug;

    public function getId(): int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }
}