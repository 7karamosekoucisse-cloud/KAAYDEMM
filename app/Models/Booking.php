<?php
declare(strict_types=1);
namespace App\Models;

use App\Enums\BookingStatus;
use App\Exceptions\InvalidTransitionException;

class Booking extends AbstractEntity
{
    private int    $tripId      = 0;
    private int    $passengerId = 0;
    private int    $seatsBooked = 1;
    private float  $totalPrice  = 0.0;
    private BookingStatus $status = BookingStatus::PENDING;
    private ?string $qrCode     = null;
    private bool   $checkedIn   = false;
    private ?\DateTimeImmutable $checkedInAt = null;

    /** @var StatusHistory[] */
    private array $history = [];

    // Runtime
    private ?string $tripDeparture = null;
    private ?string $tripArrival   = null;
    private ?\DateTimeImmutable $tripDate = null;
    private ?string $driverName    = null;
    private ?string $passengerName = null;
    private ?float  $tripPrice     = null;

    public function validate(): void {}

    public function transitionTo(
        BookingStatus $target,
        int $actorId,
        ?string $reason = null
    ): StatusHistory {
        if (!$this->status->canTransitionTo($target)) {
            throw new InvalidTransitionException(
                "Transition {$this->status->value} → {$target->value} non autorisée"
            );
        }
        $history = new StatusHistory();
        $history->setBookingId($this->id ?? 0);
        $history->setFromStatus($this->status);
        $history->setToStatus($target);
        $history->setChangedBy($actorId);
        $history->setReason($reason);
        $history->setChangedAt(new \DateTimeImmutable());

        $this->status = $target;
        return $history;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [BookingStatus::PENDING, BookingStatus::CONFIRMED], true);
    }

    public function generateQrCode(): string
    {
        $this->qrCode = bin2hex(random_bytes(16));
        return $this->qrCode;
    }

    // Getters
    public function getTripId(): int            { return $this->tripId; }
    public function getPassengerId(): int       { return $this->passengerId; }
    public function getSeatsBooked(): int       { return $this->seatsBooked; }
    public function getTotalPrice(): float      { return $this->totalPrice; }
    public function getStatus(): BookingStatus  { return $this->status; }
    public function getQrCode(): ?string        { return $this->qrCode; }
    public function isCheckedIn(): bool         { return $this->checkedIn; }
    public function getCheckedInAt(): ?\DateTimeImmutable { return $this->checkedInAt; }
    public function getHistory(): array         { return $this->history; }
    public function getTripDeparture(): ?string { return $this->tripDeparture; }
    public function getTripArrival(): ?string   { return $this->tripArrival; }
    public function getTripDate(): ?\DateTimeImmutable { return $this->tripDate; }
    public function getDriverName(): ?string    { return $this->driverName; }
    public function getPassengerName(): ?string { return $this->passengerName; }
    public function getTripPrice(): ?float      { return $this->tripPrice; }

    // Setters
    public function setTripId(int $v): void         { $this->tripId = $v; }
    public function setPassengerId(int $v): void    { $this->passengerId = $v; }
    public function setSeatsBooked(int $v): void    { $this->seatsBooked = max(1, $v); }
    public function setTotalPrice(float $v): void   { $this->totalPrice = $v; }
    public function setStatus(BookingStatus|string $v): void { $this->status = $v instanceof BookingStatus ? $v : BookingStatus::from($v); }
    public function setQrCode(?string $v): void     { $this->qrCode = $v; }
    public function setCheckedIn(bool $v): void     { $this->checkedIn = $v; }
    public function setCheckedInAt(mixed $v): void  { $this->checkedInAt = $this->parseDate($v); }
    public function setHistory(array $v): void      { $this->history = $v; }
    public function setTripDeparture(?string $v): void { $this->tripDeparture = $v; }
    public function setTripArrival(?string $v): void   { $this->tripArrival = $v; }
    public function setTripDate(mixed $v): void        { $this->tripDate = $this->parseDate($v); }
    public function setDriverName(?string $v): void    { $this->driverName = $v; }
    public function setPassengerName(?string $v): void { $this->passengerName = $v; }
    public function setTripPrice(?float $v): void      { $this->tripPrice = $v; }
}
