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
        return $this->repository->findByEmail($email);
    }

    public function login(string $email, string $password): User
    {
        $user = $this->getUserByEmail($email);

        if ($user === null || !PasswordHasher::verify($password, $user->getPasswordHash())) {
            throw new \RuntimeException('Invalid credentials', 401);
        }

        return $user;
    }

    public function signUp(string $email, string $password): void
    {
        if (!$this->valideMail($email)) {
            throw new \RuntimeException('Invalid email format.', 422);
        }
        if (!$this->validePassword($password)) {
            throw new \RuntimeException('Password must be at least 8 characters.', 422);
        }
        if ($this->getUserByEmail($email) !== null) {
            throw new \RuntimeException('Email already in use.', 422);
        }

        $this->createUser($email, $password);
    }

    public function createUser(string $email, string $password): void
    {
        $now = date('Y-m-d H:i:s');

        $user = (new User())
            ->setEmail($email)
            ->setPasswordHash(PasswordHasher::hash($password))
            ->setRole('reader')
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->repository->save($user);
    }

    private function valideMail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validePassword(string $password): bool
    {
        return strlen($password) >= 8;
    }
}
