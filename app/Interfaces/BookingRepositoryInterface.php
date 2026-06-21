<?php
declare(strict_types=1);
namespace App\Interfaces;

use App\Models\Booking;

interface BookingRepositoryInterface extends RepositoryInterface
{
    public function findByPassenger(int $passengerId, ?string $status = null): array;
    public function findByTrip(int $tripId, ?string $status = null): array;
    public function findWithDetails(int $id): ?Booking;
    public function saveHistory(\App\Models\StatusHistory $h): void;
    public function existsActiveBooking(int $tripId, int $passengerId): bool;
}
