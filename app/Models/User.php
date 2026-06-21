<?php
declare(strict_types=1);
namespace App\Models;

use App\Enums\{Role, RoleStatus};
use App\Exceptions\ValidationException;

class User extends AbstractEntity
{
    private string $email        = '';
    private string $passwordHash = '';
    private string $firstName    = '';
    private string $lastName     = '';
    private string $phone        = '';
    private string $campus       = '';
    private ?string $avatar      = null;
    private bool $isActive       = true;
    private bool $isBanned       = false;
    private ?string $rememberToken = null;
    private ?string $resetToken    = null;
    private ?\DateTimeImmutable $resetTokenExpiry = null;
    private int $loginAttempts   = 0;
    private ?\DateTimeImmutable $lockedUntil = null;
    private float $trustScore    = 50.0;
    private int $totalTrips      = 0;
    private int $totalBookings   = 0;
    private int $loyaltyPoints   = 0;

    /** @var UserRole[] */
    private array $userRoles = [];

    private ?DriverProfile $driverProfile = null;

    public function validate(): void
    {
        $errors = [];
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email invalide';
        if (strlen($this->firstName) < 2) $errors['first_name'] = 'Prénom trop court';
        if (strlen($this->lastName) < 2) $errors['last_name'] = 'Nom trop court';
        if (!preg_match('/^(77|78|70|76|33)\d{7}$/', preg_replace('/\s/', '', $this->phone))) {
            $errors['phone'] = 'Numéro de téléphone invalide (format SN)';
        }
        if (empty($this->campus)) $errors['campus'] = 'Campus requis';
        if (!empty($errors)) throw new ValidationException($errors);
    }

    public function hasRole(Role $role): bool
    {
        foreach ($this->userRoles as $ur) {
            if ($ur->getRole() === $role) return true;
        }
        return false;
    }

    public function hasApprovedRole(Role $role): bool
    {
        foreach ($this->userRoles as $ur) {
            if ($ur->getRole() === $role && $ur->getStatus() === RoleStatus::APPROVED) return true;
        }
        return false;
    }

    public function getUserRole(Role $role): ?UserRole
    {
        foreach ($this->userRoles as $ur) {
            if ($ur->getRole() === $role) return $ur;
        }
        return null;
    }

    public function getFullName(): string { return trim($this->firstName . ' ' . $this->lastName); }
    public function getInitials(): string
    {
        return strtoupper(substr($this->firstName, 0, 1) . substr($this->lastName, 0, 1));
    }

    public function isLocked(): bool
    {
        return $this->lockedUntil !== null && $this->lockedUntil > new \DateTimeImmutable();
    }

    // Getters
    public function getEmail(): string        { return $this->email; }
    public function getPasswordHash(): string  { return $this->passwordHash; }
    public function getFirstName(): string     { return $this->firstName; }
    public function getLastName(): string      { return $this->lastName; }
    public function getPhone(): string         { return $this->phone; }
    public function getCampus(): string        { return $this->campus; }
    public function getAvatar(): ?string       { return $this->avatar; }
    public function isActive(): bool           { return $this->isActive; }
    public function isBanned(): bool           { return $this->isBanned; }
    public function getRememberToken(): ?string { return $this->rememberToken; }
    public function getResetToken(): ?string   { return $this->resetToken; }
    public function getResetTokenExpiry(): ?\DateTimeImmutable { return $this->resetTokenExpiry; }
    public function getLoginAttempts(): int    { return $this->loginAttempts; }
    public function getLockedUntil(): ?\DateTimeImmutable { return $this->lockedUntil; }
    public function getTrustScore(): float     { return $this->trustScore; }
    public function getTotalTrips(): int       { return $this->totalTrips; }
    public function getTotalBookings(): int    { return $this->totalBookings; }
    public function getLoyaltyPoints(): int    { return $this->loyaltyPoints; }
    public function getUserRoles(): array      { return $this->userRoles; }
    public function getDriverProfile(): ?DriverProfile { return $this->driverProfile; }

    // Setters
    public function setEmail(string $v): void          { $this->email = strtolower(trim($v)); }
    public function setPasswordHash(string $v): void   { $this->passwordHash = $v; }
    public function setFirstName(string $v): void      { $this->firstName = trim($v); }
    public function setLastName(string $v): void       { $this->lastName = trim($v); }
    public function setPhone(string $v): void          { $this->phone = trim($v); }
    public function setCampus(string $v): void         { $this->campus = trim($v); }
    public function setAvatar(?string $v): void        { $this->avatar = $v; }
    public function setIsActive(bool $v): void         { $this->isActive = $v; }
    public function setIsBanned(bool $v): void         { $this->isBanned = $v; }
    public function setRememberToken(?string $v): void { $this->rememberToken = $v; }
    public function setResetToken(?string $v): void    { $this->resetToken = $v; }
    public function setResetTokenExpiry(?\DateTimeImmutable $v): void { $this->resetTokenExpiry = $v; }
    public function setLoginAttempts(int $v): void     { $this->loginAttempts = $v; }
    public function setLockedUntil(?\DateTimeImmutable $v): void { $this->lockedUntil = $v; }
    public function setTrustScore(float $v): void      { $this->trustScore = $v; }
    public function setTotalTrips(int $v): void        { $this->totalTrips = $v; }
    public function setTotalBookings(int $v): void     { $this->totalBookings = $v; }
    public function setLoyaltyPoints(int $v): void     { $this->loyaltyPoints = $v; }
    public function setUserRoles(array $v): void       { $this->userRoles = $v; }
    public function setDriverProfile(?DriverProfile $v): void { $this->driverProfile = $v; }
}
