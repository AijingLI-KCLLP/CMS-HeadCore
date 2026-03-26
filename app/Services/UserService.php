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

    public function signUp(string $email, string $password, ?string $name = null): User
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

        return $this->createUser($email, $password, $name);
    }

    public function createUser(string $email, string $password, ?string $name = null): User
    {
        $now = date('Y-m-d H:i:s');

        $user = (new User())
            ->setName($name)
            ->setEmail($email)
            ->setPasswordHash(PasswordHasher::hash($password))
            ->setRole('reader')
            ->setStatus('active')
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $insertedId = $this->repository->save($user);
        return $this->repository->find((int) $insertedId) ?? $user;
    }

    public function changeRole(int $userId, string $newRole, int $currentAdminId): void
    {
        $validRoles = ['admin', 'editor', 'author', 'reader'];

        if (!in_array($newRole, $validRoles, true)) {
            throw new \RuntimeException('Invalid role.', 422);
        }
        if ($userId === $currentAdminId) {
            throw new \RuntimeException('Cannot change your own role.', 403);
        }

        $user = $this->getUserById($userId);
        if ($user === null) {
            throw new \RuntimeException('User not found.', 404);
        }

        $user->setRole($newRole)->setUpdatedAt(date('Y-m-d H:i:s'));
        $this->repository->update($user);
    }

    public function deleteUser(int $userId, int $currentAdminId): void
    {
        if ($userId === $currentAdminId) {
            throw new \RuntimeException('Cannot delete your own account.', 403);
        }

        $user = $this->getUserById($userId);
        if ($user === null) {
            throw new \RuntimeException('User not found.', 404);
        }

        $user->setStatus('deleted')
             ->setEmail('deleted_' . $userId . '@deleted.invalid')
             ->setUpdatedAt(date('Y-m-d H:i:s'));

        $this->repository->update($user);
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
