<?php
declare(strict_types=1);
namespace App\Models;

class WaitlistEntry extends AbstractEntity
{
    private int $tripId      = 0;
    private int $passengerId = 0;
    private int $seatsWanted = 1;
    private int $position    = 0;

    private ?string $passengerName = null;

    public function validate(): void {}

    public function getTripId(): int        { return $this->tripId; }
    public function getPassengerId(): int   { return $this->passengerId; }
    public function getSeatsWanted(): int   { return $this->seatsWanted; }
    public function getPosition(): int      { return $this->position; }
    public function getPassengerName(): ?string { return $this->passengerName; }

    public function setTripId(int $v): void        { $this->tripId = $v; }
    public function setPassengerId(int $v): void   { $this->passengerId = $v; }
    public function setSeatsWanted(int $v): void   { $this->seatsWanted = max(1, $v); }
    public function setPosition(int $v): void      { $this->position = $v; }
    public function setPassengerName(?string $v): void { $this->passengerName = $v; }
}
