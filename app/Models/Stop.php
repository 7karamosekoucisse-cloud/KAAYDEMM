<?php
declare(strict_types=1);
namespace App\Models;

class Stop extends AbstractEntity
{
    private int    $tripId     = 0;
    private string $city       = '';
    private int    $position   = 0;
    private float  $extraPrice = 0.0;

    public function validate(): void {}

    public function getTripId(): int       { return $this->tripId; }
    public function getCity(): string      { return $this->city; }
    public function getPosition(): int     { return $this->position; }
    public function getExtraPrice(): float { return $this->extraPrice; }

    public function setTripId(int $v): void        { $this->tripId = $v; }
    public function setCity(string $v): void       { $this->city = trim($v); }
    public function setPosition(int $v): void      { $this->position = $v; }
    public function setExtraPrice(float $v): void  { $this->extraPrice = max(0.0, $v); }
}
