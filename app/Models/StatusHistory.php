<?php
declare(strict_types=1);
namespace App\Models;

use App\Enums\BookingStatus;

class StatusHistory extends AbstractEntity
{
    private int    $bookingId  = 0;
    private ?BookingStatus $fromStatus = null;
    private BookingStatus $toStatus   = BookingStatus::PENDING;
    private ?int   $changedBy  = null;
    private ?string $reason    = null;
    private ?\DateTimeImmutable $changedAt = null;

    private ?string $changedByName = null;

    public function validate(): void {}

    public function getBookingId(): int              { return $this->bookingId; }
    public function getFromStatus(): ?BookingStatus  { return $this->fromStatus; }
    public function getToStatus(): BookingStatus     { return $this->toStatus; }
    public function getChangedBy(): ?int             { return $this->changedBy; }
    public function getReason(): ?string             { return $this->reason; }
    public function getChangedAt(): ?\DateTimeImmutable { return $this->changedAt; }
    public function getChangedByName(): ?string      { return $this->changedByName; }

    public function setBookingId(int $v): void           { $this->bookingId = $v; }
    public function setFromStatus(BookingStatus|string|null $v): void { $this->fromStatus = $v === null ? null : ($v instanceof BookingStatus ? $v : BookingStatus::from($v)); }
    public function setToStatus(BookingStatus|string $v): void { $this->toStatus = $v instanceof BookingStatus ? $v : BookingStatus::from($v); }
    public function setChangedBy(?int $v): void          { $this->changedBy = $v; }
    public function setReason(?string $v): void          { $this->reason = $v; }
    public function setChangedAt(mixed $v): void         { $this->changedAt = $this->parseDate($v); }
    public function setChangedByName(?string $v): void   { $this->changedByName = $v; }
}
