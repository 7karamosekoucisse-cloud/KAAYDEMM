<?php
declare(strict_types=1);
namespace App\Services;

use App\Repositories\{TripRepository, BookingRepository, UserRepository, ReviewRepository};

class StatisticsService
{
    public function __construct(
        private readonly TripRepository    $tripRepo,
        private readonly BookingRepository $bookingRepo,
        private readonly UserRepository    $userRepo,
        private readonly ReviewRepository  $reviewRepo
    ) {}

    public function getGlobalStats(): array
    {
        return [
            'total_users'    => $this->userRepo->countAll(),
            'total_trips'    => $this->tripRepo->countAll(),
            'total_bookings' => $this->bookingRepo->countAll(),
            'occupancy_rate' => $this->tripRepo->getOccupancyRate(),
        ];
    }

    public function getAdminDashboard(): array
    {
        return [
            'global'          => $this->getGlobalStats(),
            'monthly_trips'   => $this->tripRepo->getMonthlyStats(),
            'monthly_revenue' => $this->bookingRepo->getMonthlyStats(),
            'popular_routes'  => $this->tripRepo->findPopularRoutes(5),
            'top_drivers'     => $this->userRepo->getLeaderboard(5),
        ];
    }

    public function getDriverDashboard(int $driverId): array
    {
        $trips    = $this->tripRepo->findByDriver($driverId);
        $upcoming = $this->tripRepo->findUpcomingByDriver($driverId);
        $stats    = $this->userRepo->getUserStats($driverId);
        $reviews  = $this->reviewRepo->findByTarget($driverId);
        $details  = $this->reviewRepo->getDetailedStats($driverId);

        $totalEarnings = 0.0;
        $totalPassengers = 0;
        foreach ($trips as $t) {
            $bookings = $this->bookingRepo->findByTrip($t->getId(), 'COMPLETED');
            foreach ($bookings as $b) {
                $totalEarnings  += $b->getTotalPrice();
                $totalPassengers += $b->getSeatsBooked();
            }
        }

        return compact('trips', 'upcoming', 'stats', 'reviews', 'details', 'totalEarnings', 'totalPassengers');
    }

    public function getPassengerDashboard(int $passengerId): array
    {
        $bookings  = $this->bookingRepo->findByPassenger($passengerId);
        $stats     = $this->userRepo->getUserStats($passengerId);
        $reviews   = $this->reviewRepo->findByTarget($passengerId);

        $totalSpent = array_sum(array_map(
            fn($b) => $b->getStatus()->value === 'COMPLETED' ? $b->getTotalPrice() : 0,
            $bookings
        ));

        return compact('bookings', 'stats', 'reviews', 'totalSpent');
    }

    public function exportBookingsCsv(): string
    {
        $rows = $this->bookingRepo->findAll(10000);
        $out  = "ID,Trajet,Passager,Places,Total,Statut,Date\n";
        foreach ($rows as $b) {
            $out .= implode(',', [
                $b->getId(),
                '"' . ($b->getTripDeparture() ?? '') . ' → ' . ($b->getTripArrival() ?? '') . '"',
                '"' . ($b->getPassengerName() ?? '') . '"',
                $b->getSeatsBooked(),
                $b->getTotalPrice(),
                $b->getStatus()->value,
                $b->getCreatedAt()?->format('Y-m-d H:i') ?? '',
            ]) . "\n";
        }
        return $out;
    }
}
