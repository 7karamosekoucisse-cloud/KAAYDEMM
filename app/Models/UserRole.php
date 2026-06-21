<?php
declare(strict_types=1);
namespace App\Models;

use App\Enums\{Role, RoleStatus};

class UserRole extends AbstractEntity
{
    private int    $userId        = 0;
    private Role   $role          = Role::PASSENGER;
    private RoleStatus $status    = RoleStatus::PENDING;
    private ?\DateTimeImmutable $grantedAt    = null;
    private ?int   $validatedBy   = null;
    private ?\DateTimeImmutable $validatedAt  = null;
    private ?string $rejectionReason = null;

    public function validate(): void {}

    public function approve(int $adminId): void
    {
        $this->status      = RoleStatus::APPROVED;
        $this->validatedBy = $adminId;
        $this->validatedAt = new \DateTimeImmutable();
    }

    public function reject(int $adminId, string $reason = ''): void
    {
        $this->status          = RoleStatus::REJECTED;
        $this->validatedBy     = $adminId;
        $this->validatedAt     = new \DateTimeImmutable();
        $this->rejectionReason = $reason;
    }

    public function revoke(): void { $this->status = RoleStatus::REVOKED; }

    // Getters
    public function getUserId(): int          { return $this->userId; }
    public function getRole(): Role           { return $this->role; }
    public function getStatus(): RoleStatus   { return $this->status; }
    public function getGrantedAt(): ?\DateTimeImmutable { return $this->grantedAt; }
    public function getValidatedBy(): ?int    { return $this->validatedBy; }
    public function getValidatedAt(): ?\DateTimeImmutable { return $this->validatedAt; }
    public function getRejectionReason(): ?string { return $this->rejectionReason; }

    // Setters
    public function setUserId(int $v): void       { $this->userId = $v; }
    public function setRole(Role|string $v): void { $this->role = $v instanceof Role ? $v : Role::from($v); }
    public function setStatus(RoleStatus|string $v): void { $this->status = $v instanceof RoleStatus ? $v : RoleStatus::from($v); }
    public function setGrantedAt(mixed $v): void  { $this->grantedAt = $this->parseDate($v); }
    public function setValidatedBy(?int $v): void { $this->validatedBy = $v; }
    public function setValidatedAt(mixed $v): void { $this->validatedAt = $this->parseDate($v); }
    public function setRejectionReason(?string $v): void { $this->rejectionReason = $v; }
}
