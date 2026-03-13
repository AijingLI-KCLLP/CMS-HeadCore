<?php

namespace App\Services;

use App\Entities\User;
use App\Repositories\UserRepository;
use Core\Auth\PasswordHasher;
use Core\Services\AbstractService;

class UserService extends AbstractService
{
    public function __construct()
    {
        parent::__construct(new UserRepository());
    }

    /** @return User[] */
    public function listUsers(): array
    {
        return $this->repository->findAll();
    }

    public function getUserById(int $id): ?User
    {
        return $this->repository->find($id);
    }

    public function getUserByEmail(string $email): ?User
    {
        /** @var UserRepository $repository */
        return $this->repository->findByEmail($email);
    }

    public function createUser(string $name, string $email, string $password): string
    {
        if ($this->getUserByEmail($email) !== null) {
            throw new \RuntimeException('Email already in use.', 409);
        }

        $user = (new User())
            ->setName($name)
            ->setEmail($email)
            ->setPassword(PasswordHasher::hash($password));

        return $this->repository->save($user);
    }
}