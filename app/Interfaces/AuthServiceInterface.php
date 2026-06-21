<?php
declare(strict_types=1);
namespace App\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(array $data): User;
    public function login(string $email, string $password, string $ip): User;
    public function logout(): void;
    public function currentUser(): ?User;
    public function isAuthenticated(): bool;
}
