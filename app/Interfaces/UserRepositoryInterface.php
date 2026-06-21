<?php
declare(strict_types=1);
namespace App\Interfaces;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByRememberToken(string $token): ?User;
    public function findByResetToken(string $token): ?User;
    public function findPendingDrivers(): array;
    public function search(string $query, int $limit = 20): array;
}
