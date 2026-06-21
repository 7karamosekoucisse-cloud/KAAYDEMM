<?php
declare(strict_types=1);
namespace App\Services;

use Core\{Database, EventDispatcher, Logger};
use App\Models\{User, Trip};
use App\Enums\{TripStatus, Role};
use App\Interfaces\{TripRepositoryInterface, NotifierInterface};
use App\Exceptions\{AuthorizationException, NotFoundException, BookingException};
use App\Helpers\Validator;
use App\Repositories\BookingRepository;

class TripService
{
    public function __construct(
        private readonly TripRepositoryInterface $tripRepo,
        private readonly BookingRepository       $bookingRepo,
        private readonly NotifierInterface       $notifier
    ) {}

    public function publish(User $driver, array $data): Trip
    {
        if (!$driver->hasApprovedRole(Role::DRIVER)) {
            throw new AuthorizationException('Votre compte conducteur n\'est pas encore validé.');
        }

        Validator::make()
            ->required($data, ['departure_city', 'arrival_city', 'departure_at', 'price_per_seat', 'seats_offered'])
            ->notSame($data, 'departure_city', 'arrival_city', 'Départ et arrivée ne peuvent être identiques')
            ->dateInFuture($data, 'departure_at')
            ->numeric($data, 'price_per_seat', 0)
            ->between($data, 'seats_offered', 1, 8)
            ->throwIfErrors();

        $trip = new Trip();
        $trip->setDriverId($driver->getId());
        $trip->setDepartureCity($data['departure_city']);
        $trip->setArrivalCity($data['arrival_city']);
        $trip->setDepartureAt($data['departure_at']);
        $trip->setPricePerSeat((float)$data['price_per_seat']);
        $trip->setSeatsOffered((int)$data['seats_offered']);
        $trip->setSeatsAvailable((int)$data['seats_offered']);
        $trip->setDescription($data['description'] ?? null);
        $trip->setIsRecurring((bool)($data['is_recurring'] ?? false));
        $trip->setRecurringDays($data['recurring_days'] ?? null);

        // Calculate distance & CO2 if provided
        if (!empty($data['distance_km'])) {
            $distKm = (float)$data['distance_km'];
            $trip->setDistanceKm($distKm);
            $co2 = $this->calculateCo2($distKm, (int)$data['seats_offered']);
            $trip->setCo2Saved($co2);
        }

        $trip->validate();

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $this->tripRepo->save($trip);
            if (!empty($data['stops']) && is_array($data['stops'])) {
                $this->tripRepo->saveStops($trip->getId(), $data['stops']);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        EventDispatcher::getInstance()->emit('trip.published', $trip);
        Logger::info('Trip published', ['trip_id' => $trip->getId(), 'driver' => $driver->getId()]);
        return $trip;
    }

    public function update(User $driver, int $tripId, array $data): Trip
    {
        $trip = $this->tripRepo->find($tripId);
        if (!$trip) throw new NotFoundException('Trajet introuvable');
        if ($trip->getDriverId() !== $driver->getId()) throw new AuthorizationException();

        $confirmed = $this->bookingRepo->findByTrip($tripId, 'CONFIRMED');
        if (!empty($confirmed)) {
            throw new BookingException('Impossible de modifier un trajet avec des réservations confirmées.');
        }

        if (!empty($data['departure_city'])) $trip->setDepartureCity($data['departure_city']);
        if (!empty($data['arrival_city']))   $trip->setArrivalCity($data['arrival_city']);
        if (!empty($data['departure_at']))   $trip->setDepartureAt($data['departure_at']);
        if (isset($data['price_per_seat']))  $trip->setPricePerSeat((float)$data['price_per_seat']);
        if (!empty($data['description']))    $trip->setDescription($data['description']);

        $this->tripRepo->save($trip);
        if (!empty($data['stops'])) {
            $this->tripRepo->saveStops($tripId, $data['stops']);
        }
        return $trip;
    }

    public function cancel(User $actor, int $tripId, string $reason = ''): void
    {
        $trip = $this->tripRepo->find($tripId);
        if (!$trip) throw new NotFoundException('Trajet introuvable');

        $isDriver = $trip->getDriverId() === $actor->getId();
        $isAdmin  = $actor->hasApprovedRole(Role::ADMIN);
        if (!$isDriver && !$isAdmin) throw new AuthorizationException();

        if ($trip->getStatus() === TripStatus::COMPLETED) {
            throw new BookingException('Un trajet terminé ne peut pas être annulé.');
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $trip->setStatus(TripStatus::CANCELLED);
            $this->tripRepo->save($trip);
            $cancelledBookings = $this->bookingRepo->cancelAllForTrip($tripId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        Logger::info('Trip cancelled', ['trip_id' => $tripId, 'actor' => $actor->getId()]);
    }

    public function complete(User $driver, int $tripId): void
    {
        $trip = $this->tripRepo->find($tripId);
        if (!$trip) throw new NotFoundException('Trajet introuvable');
        if ($trip->getDriverId() !== $driver->getId()) throw new AuthorizationException();
        if ($trip->getStatus() === TripStatus::COMPLETED) {
            throw new BookingException('Ce trajet est déjà terminé.');
        }
        if ($trip->getStatus() === TripStatus::CANCELLED) {
            throw new BookingException('Un trajet annulé ne peut pas être terminé.');
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $trip->setStatus(TripStatus::COMPLETED);
            $this->tripRepo->save($trip);
            $this->bookingRepo->completeAllForTrip($tripId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        Logger::info('Trip completed', ['trip_id' => $tripId, 'driver' => $driver->getId()]);
    }

    public function closePastTrips(): int
    {
        $count = $this->tripRepo->closePastTrips();
        if ($count > 0) Logger::info("$count past trips closed");
        return $count;
    }

    private function calculateCo2(float $distKm, int $passengers): float
    {
        $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        $gPerKm = $cfg['co2']['car_per_km'] ?? 120.0;
        return round($distKm * $gPerKm * ($passengers - 1) / 1000, 2);
    }

    public function suggestPrice(float $distanceKm): float
    {
        $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        return round($distanceKm * ($cfg['price']['per_km'] ?? 15), -2);
    }
}
