<?php
declare(strict_types=1);
namespace App\Interfaces;

use App\Models\{Trip, AbstractEntity};

interface TripRepositoryInterface extends RepositoryInterface
{
    public function search(array $criteria, int $page, int $perPage): array;
    public function countSearch(array $criteria): int;
    public function decrementSeats(int $tripId, int $n): bool;
    public function incrementSeats(int $tripId, int $n): bool;
    public function findByDriver(int $driverId): array;
    public function findUpcomingByDriver(int $driverId): array;
    public function closePastTrips(): int;
    public function findPopularRoutes(int $limit = 10): array;
}
