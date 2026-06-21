<?php
declare(strict_types=1);
namespace App\Models;

use App\Enums\TripStatus;
use App\Exceptions\ValidationException;

class Trip extends AbstractEntity
{
    private int    $driverId       = 0;
    private string $departureCity  = '';
    private string $arrivalCity    = '';
    private ?\DateTimeImmutable $departureAt = null;
    private float  $pricePerSeat   = 0.0;
    private int    $seatsOffered   = 1;
    private int    $seatsAvailable = 1;
    private TripStatus $status     = TripStatus::OPEN;
    private ?string $description   = null;
    private float  $distanceKm     = 0.0;
    private float  $co2Saved       = 0.0;
    private bool   $isRecurring    = false;
    private ?string $recurringDays = null;

    /** @var Stop[] */
    private array $stops = [];

    // Runtime (non-persisté)
    private ?string $driverName   = null;
    private ?string $driverAvatar = null;
    private ?float  $driverRating = null;
    private float   $driverTrustScore = 0.0;

    public function validate(): void
    {
        $errors = [];
        if (empty($this->departureCity)) $errors['departure_city'] = 'Ville de départ requise';
        if (empty($this->arrivalCity))   $errors['arrival_city']   = 'Ville d\'arrivée requise';
        if ($this->departureCity === $this->arrivalCity) $errors['arrival_city'] = 'Départ et arrivée identiques';
        if ($this->departureAt === null || $this->departureAt <= new \DateTimeImmutable()) {
            $errors['departure_at'] = 'La date de départ doit être dans le futur';
        }
        if ($this->pricePerSeat < 0)                  $errors['price_per_seat']  = 'Le prix ne peut être négatif';
        if ($this->seatsOffered < 1 || $this->seatsOffered > 8) $errors['seats_offered'] = 'Places entre 1 et 8';
        if (!empty($errors)) throw new ValidationException($errors);
    }

    public function hasAvailableSeats(int $n = 1): bool
    {
        return $this->status === TripStatus::OPEN && $this->seatsAvailable >= $n;
    }

    public function isPast(): bool
    {
        return $this->departureAt !== null && $this->departureAt < new \DateTimeImmutable();
    }

    public function getOccupancyRate(): float
    {
        if ($this->seatsOffered === 0) return 0.0;
        return round(($this->seatsOffered - $this->seatsAvailable) / $this->seatsOffered * 100, 1);
    }

    // Getters
    public function getDriverId(): int         { return $this->driverId; }
    public function getDepartureCity(): string  { return $this->departureCity; }
    public function getArrivalCity(): string    { return $this->arrivalCity; }
    public function getDepartureAt(): ?\DateTimeImmutable { return $this->departureAt; }
    public function getPricePerSeat(): float    { return $this->pricePerSeat; }
    public function getSeatsOffered(): int      { return $this->seatsOffered; }
    public function getSeatsAvailable(): int    { return $this->seatsAvailable; }
    public function getStatus(): TripStatus     { return $this->status; }
    public function getDescription(): ?string   { return $this->description; }
    public function getDistanceKm(): float      { return $this->distanceKm; }
    public function getCo2Saved(): float        { return $this->co2Saved; }
    public function isRecurring(): bool         { return $this->isRecurring; }
    public function getRecurringDays(): ?string { return $this->recurringDays; }
    public function getStops(): array           { return $this->stops; }
    public function getDriverName(): ?string    { return $this->driverName; }
    public function getDriverAvatar(): ?string  { return $this->driverAvatar; }
    public function getDriverRating(): ?float   { return $this->driverRating; }
    public function getDriverTrustScore(): float { return $this->driverTrustScore; }

    // Setters
    public function setDriverId(int $v): void          { $this->driverId = $v; }
    public function setDepartureCity(string $v): void  { $this->departureCity = trim($v); }
    public function setArrivalCity(string $v): void    { $this->arrivalCity = trim($v); }
    public function setDepartureAt(mixed $v): void     { $this->departureAt = $this->parseDate($v); }
    public function setPricePerSeat(float|string $v): void { $this->pricePerSeat = max(0.0, (float)$v); }
    public function setSeatsOffered(int $v): void      { $this->seatsOffered = max(1, min(8, $v)); }
    public function setSeatsAvailable(int $v): void    { $this->seatsAvailable = max(0, $v); }
    public function setStatus(TripStatus|string $v): void { $this->status = $v instanceof TripStatus ? $v : TripStatus::from($v); }
    public function setDescription(?string $v): void   { $this->description = $v; }
    public function setDistanceKm(float $v): void      { $this->distanceKm = $v; }
    public function setCo2Saved(float $v): void        { $this->co2Saved = $v; }
    public function setIsRecurring(bool $v): void      { $this->isRecurring = $v; }
    public function setRecurringDays(?string $v): void { $this->recurringDays = $v; }
    public function setStops(array $v): void           { $this->stops = $v; }
    public function setDriverName(?string $v): void    { $this->driverName = $v; }
    public function setDriverAvatar(?string $v): void  { $this->driverAvatar = $v; }
    public function setDriverRating(?float $v): void   { $this->driverRating = $v; }
    public function setDriverTrustScore(float $v): void { $this->driverTrustScore = $v; }
}
