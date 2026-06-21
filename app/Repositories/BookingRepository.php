<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\{Booking, StatusHistory, AbstractEntity};
use App\Interfaces\BookingRepositoryInterface;

class BookingRepository extends AbstractRepository implements BookingRepositoryInterface
{
    protected string $table      = 'bookings';
    protected string $modelClass = Booking::class;

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var Booking $entity */
        if ($entity->isNew()) {
            $this->execute(
                'INSERT INTO bookings (trip_id, passenger_id, seats_booked, total_price, status, qr_code, created_at) VALUES (?,?,?,?,?,?,NOW())',
                [
                    $entity->getTripId(), $entity->getPassengerId(),
                    $entity->getSeatsBooked(), $entity->getTotalPrice(),
                    $entity->getStatus()->value, $entity->getQrCode(),
                ]
            );
            $entity->setId((int)$this->pdo->lastInsertId());
        } else {
            $this->execute(
                'UPDATE bookings SET status=?, checked_in=?, checked_in_at=?, updated_at=NOW() WHERE id=?',
                [
                    $entity->getStatus()->value,
                    (int)$entity->isCheckedIn(),
                    $entity->getCheckedInAt()?->format('Y-m-d H:i:s'),
                    $entity->getId(),
                ]
            );
        }
        return $entity;
    }

    protected function hydrate(array $row): Booking
    {
        $b = new Booking();
        $b->setId((int)$row['id']);
        $b->setTripId((int)$row['trip_id']);
        $b->setPassengerId((int)$row['passenger_id']);
        $b->setSeatsBooked((int)$row['seats_booked']);
        $b->setTotalPrice((float)$row['total_price']);
        $b->setStatus($row['status']);
        $b->setQrCode($row['qr_code'] ?? null);
        $b->setCheckedIn((bool)($row['checked_in'] ?? false));
        $b->setCheckedInAt($row['checked_in_at'] ?? null);
        $b->setCreatedAt(!empty($row['created_at']) ? new \DateTimeImmutable($row['created_at']) : null);
        // JOIN fields
        if (isset($row['departure_city'])) $b->setTripDeparture($row['departure_city']);
        if (isset($row['arrival_city']))   $b->setTripArrival($row['arrival_city']);
        if (isset($row['departure_at']))   $b->setTripDate($row['departure_at']);
        if (isset($row['driver_name']))    $b->setDriverName($row['driver_name']);
        if (isset($row['passenger_name'])) $b->setPassengerName($row['passenger_name']);
        if (isset($row['price_per_seat'])) $b->setTripPrice((float)$row['price_per_seat']);
        return $b;
    }

    public function findWithDetails(int $id): ?Booking
    {
        $row = $this->fetchOne(
            "SELECT b.*,
                    t.departure_city, t.arrival_city, t.departure_at, t.price_per_seat,
                    CONCAT(d.first_name,' ',d.last_name) AS driver_name,
                    CONCAT(p.first_name,' ',p.last_name) AS passenger_name
             FROM bookings b
             JOIN trips t ON t.id = b.trip_id
             JOIN users d ON d.id = t.driver_id
             JOIN users p ON p.id = b.passenger_id
             WHERE b.id = ?",
            [$id]
        );
        if (!$row) return null;
        $booking = $this->hydrate($row);
        $booking->setHistory($this->findHistory($id));
        return $booking;
    }

    private function findHistory(int $bookingId): array
    {
        $rows = $this->fetchAll(
            "SELECT bsh.*, CONCAT(u.first_name,' ',u.last_name) AS changed_by_name
             FROM booking_status_history bsh
             LEFT JOIN users u ON u.id = bsh.changed_by
             WHERE bsh.booking_id = ? ORDER BY bsh.changed_at ASC",
            [$bookingId]
        );
        return array_map(function($r) {
            $h = new StatusHistory();
            $h->setId((int)$r['id']);
            $h->setBookingId((int)$r['booking_id']);
            $h->setFromStatus($r['from_status'] ?? null);
            $h->setToStatus($r['to_status']);
            $h->setChangedBy($r['changed_by'] ? (int)$r['changed_by'] : null);
            $h->setReason($r['reason'] ?? null);
            $h->setChangedAt($r['changed_at'] ?? null);
            $h->setChangedByName($r['changed_by_name'] ?? null);
            return $h;
        }, $rows);
    }

    public function saveHistory(StatusHistory $h): void
    {
        $this->execute(
            'INSERT INTO booking_status_history (booking_id, from_status, to_status, changed_by, reason, changed_at) VALUES (?,?,?,?,?,NOW())',
            [
                $h->getBookingId(),
                $h->getFromStatus()?->value,
                $h->getToStatus()->value,
                $h->getChangedBy(),
                $h->getReason(),
            ]
        );
    }

    public function findByPassenger(int $passengerId, ?string $status = null): array
    {
        $where = 'b.passenger_id = ?';
        $params = [$passengerId];
        if ($status) { $where .= ' AND b.status = ?'; $params[] = $status; }
        $rows = $this->fetchAll(
            "SELECT b.*, t.departure_city, t.arrival_city, t.departure_at, t.price_per_seat,
                    CONCAT(d.first_name,' ',d.last_name) AS driver_name
             FROM bookings b
             JOIN trips t ON t.id = b.trip_id
             JOIN users d ON d.id = t.driver_id
             WHERE $where ORDER BY b.created_at DESC",
            $params
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function findByTrip(int $tripId, ?string $status = null): array
    {
        $where = 'b.trip_id = ?';
        $params = [$tripId];
        if ($status) { $where .= ' AND b.status = ?'; $params[] = $status; }
        $rows = $this->fetchAll(
            "SELECT b.*, CONCAT(p.first_name,' ',p.last_name) AS passenger_name, p.avatar AS passenger_avatar
             FROM bookings b JOIN users p ON p.id = b.passenger_id
             WHERE $where ORDER BY b.created_at ASC",
            $params
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function existsActiveBooking(int $tripId, int $passengerId): bool
    {
        $row = $this->fetchOne(
            "SELECT id FROM bookings WHERE trip_id=? AND passenger_id=? AND status IN ('PENDING','CONFIRMED')",
            [$tripId, $passengerId]
        );
        return $row !== null;
    }

    public function countAll(): int
    {
        return (int)($this->fetchOne('SELECT COUNT(*) as c FROM bookings')['c'] ?? 0);
    }

    public function getMonthlyStats(): array
    {
        return $this->fetchAll(
            "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as count, SUM(total_price) as revenue
             FROM bookings WHERE status IN ('CONFIRMED','COMPLETED')
             AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month ORDER BY month"
        );
    }

    public function completeBookingsForTrip(int $tripId): void
    {
        $this->execute(
            "UPDATE bookings SET status='COMPLETED', updated_at=NOW() WHERE trip_id=? AND status='CONFIRMED'",
            [$tripId]
        );
    }

    public function cancelAllForTrip(int $tripId): array
    {
        $rows = $this->fetchAll(
            "SELECT id, passenger_id, seats_booked FROM bookings WHERE trip_id=? AND status IN ('PENDING','CONFIRMED')",
            [$tripId]
        );
        if ($rows) {
            $this->execute(
                "UPDATE bookings SET status='CANCELLED', updated_at=NOW() WHERE trip_id=? AND status IN ('PENDING','CONFIRMED')",
                [$tripId]
            );
        }
        return $rows;
    }


}
