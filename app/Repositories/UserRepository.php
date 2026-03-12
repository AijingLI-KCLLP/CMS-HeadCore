<?php

namespace App\Repositories;

use App\Entities\User;
use Core\Repositories\AbstractRepository;

class UserRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
}