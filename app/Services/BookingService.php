<?php
declare(strict_types=1);
namespace App\Services;

use Core\{Database, EventDispatcher, Logger};
use App\Services\EmailService;
use App\Models\{User, Booking, StatusHistory, WaitlistEntry};
use App\Enums\BookingStatus;
use App\Interfaces\{BookingRepositoryInterface, TripRepositoryInterface, NotifierInterface, UserRepositoryInterface};
use App\Exceptions\{AuthorizationException, NotFoundException, BookingException, SeatUnavailableException};
use App\Repositories\{WaitlistRepository};

class BookingService
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepo,
        private readonly TripRepositoryInterface    $tripRepo,
        private readonly NotifierInterface          $notifier,
        private readonly UserRepositoryInterface    $userRepo
    ) {}

    public function book(User $passenger, int $tripId, int $seats): Booking
    {
        $trip = $this->tripRepo->find($tripId);
        if (!$trip) throw new NotFoundException('Trajet introuvable');

        if ($trip->getDriverId() === $passenger->getId()) {
            throw new BookingException('Vous ne pouvez pas réserver votre propre trajet.');
        }

        if ($this->bookingRepo->existsActiveBooking($tripId, $passenger->getId())) {
            throw new BookingException('Vous avez déjà une réservation active sur ce trajet.');
        }

        if ($seats < 1 || $seats > 8) {
            throw new BookingException('Nombre de places invalide.');
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            if (!$this->tripRepo->decrementSeats($tripId, $seats)) {
                $db->rollBack();
                throw new SeatUnavailableException();
            }

            $qrCode = bin2hex(random_bytes(16));
            $booking = new Booking();
            $booking->setTripId($tripId);
            $booking->setPassengerId($passenger->getId());
            $booking->setSeatsBooked($seats);
            $booking->setTotalPrice($trip->getPricePerSeat() * $seats);
            $booking->setStatus(BookingStatus::PENDING);
            $booking->setQrCode($qrCode);
            $this->bookingRepo->save($booking);

            $history = new StatusHistory();
            $history->setBookingId($booking->getId());
            $history->setFromStatus(null);
            $history->setToStatus(BookingStatus::PENDING);
            $history->setChangedBy($passenger->getId());
            $this->bookingRepo->saveHistory($history);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        // Notify driver
        try {
            $tripFull = $this->tripRepo->find($tripId);
            // Load driver user would require userRepo, using event instead
            EventDispatcher::getInstance()->emit('booking.created', [
                'booking' => $booking,
                'trip'    => $trip,
                'passenger' => $passenger,
            ]);
        } catch (\Throwable) {}

        Logger::info('Booking created', ['booking_id' => $booking->getId(), 'passenger' => $passenger->getId()]);
        return $booking;
    }

    public function confirm(User $driver, int $bookingId): Booking
    {
        $booking = $this->bookingRepo->findWithDetails($bookingId);
        if (!$booking) throw new NotFoundException('Réservation introuvable');

        $trip = $this->tripRepo->find($booking->getTripId());
        if (!$trip || $trip->getDriverId() !== $driver->getId()) {
            throw new AuthorizationException('Vous n\'êtes pas le conducteur de ce trajet.');
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $history = $booking->transitionTo(BookingStatus::CONFIRMED, $driver->getId());
            $this->bookingRepo->save($booking);
            $this->bookingRepo->saveHistory($history);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        EventDispatcher::getInstance()->emit('booking.confirmed', ['booking' => $booking, 'driver' => $driver]);

        // Email de confirmation au passager
        try {
            $passenger = $this->userRepo->find($booking->getPassengerId());
            if ($passenger && $trip) {
                (new EmailService())->sendBookingConfirmed(
                    $passenger->getEmail(),
                    $passenger->getFirstName(),
                    $trip->getDepartureCity(),
                    $trip->getArrivalCity(),
                    $trip->getDepartureAt() ? $trip->getDepartureAt()->format('d/m/Y H:i') : '',
                    (float)$booking->getTotalPrice()
                );
            }
        } catch (\Throwable) {}

        // Email au conducteur aussi (optionnel - notif nouvelle réservation)
        return $booking;
    }

    public function cancel(User $actor, int $bookingId, string $reason = ''): Booking
    {
        $booking = $this->bookingRepo->findWithDetails($bookingId);
        if (!$booking) throw new NotFoundException('Réservation introuvable');

        $trip = $this->tripRepo->find($booking->getTripId());
        $isPassenger = $booking->getPassengerId() === $actor->getId();
        $isDriver    = $trip && $trip->getDriverId() === $actor->getId();
        $isAdmin     = $actor->hasApprovedRole(\App\Enums\Role::ADMIN);

        if (!$isPassenger && !$isDriver && !$isAdmin) {
            throw new AuthorizationException();
        }
        if (!$booking->isCancellable()) {
            throw new BookingException('Cette réservation ne peut plus être annulée.');
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $prevStatus = $booking->getStatus();
            $history = $booking->transitionTo(BookingStatus::CANCELLED, $actor->getId(), $reason ?: null);
            $this->bookingRepo->save($booking);
            $this->bookingRepo->saveHistory($history);
            $this->tripRepo->incrementSeats($booking->getTripId(), $booking->getSeatsBooked());
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        EventDispatcher::getInstance()->emit('booking.cancelled', ['booking' => $booking, 'actor' => $actor]);
        return $booking;
    }

    public function complete(int $bookingId, int $actorId): Booking
    {
        $booking = $this->bookingRepo->findWithDetails($bookingId);
        if (!$booking) throw new NotFoundException('Réservation introuvable');

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $history = $booking->transitionTo(BookingStatus::COMPLETED, $actorId);
            $this->bookingRepo->save($booking);
            $this->bookingRepo->saveHistory($history);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return $booking;
    }

    public function checkIn(User $driver, string $qrCode): Booking
    {
        $row = Database::getInstance()->pdo()->prepare(
            "SELECT id FROM bookings WHERE qr_code=? AND status='CONFIRMED'"
        );
        $row->execute([$qrCode]);
        $data = $row->fetch();
        if (!$data) throw new NotFoundException('QR Code invalide ou réservation non confirmée');

        $booking = $this->bookingRepo->findWithDetails((int)$data['id']);
        $trip = $this->tripRepo->find($booking->getTripId());
        if ($trip->getDriverId() !== $driver->getId()) throw new AuthorizationException();

        $booking->setCheckedIn(true);
        $booking->setCheckedInAt(new \DateTimeImmutable());
        $this->bookingRepo->save($booking);

        return $booking;
    }
}
