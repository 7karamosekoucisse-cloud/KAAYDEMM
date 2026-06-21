<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\{Trip, Stop, AbstractEntity};
use App\Interfaces\TripRepositoryInterface;
use App\Enums\TripStatus;

class TripRepository extends AbstractRepository implements TripRepositoryInterface
{
    protected string $table      = 'trips';
    protected string $modelClass = Trip::class;

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var Trip $entity */
        if ($entity->isNew()) {
            $sql = 'INSERT INTO trips (driver_id, departure_city, arrival_city, departure_at, price_per_seat, seats_offered, seats_available, status, description, distance_km, co2_saved, is_recurring, recurring_days, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())';
            $this->execute($sql, [
                $entity->getDriverId(),
                $entity->getDepartureCity(), $entity->getArrivalCity(),
                $entity->getDepartureAt()?->format('Y-m-d H:i:s'),
                $entity->getPricePerSeat(),
                $entity->getSeatsOffered(), $entity->getSeatsAvailable(),
                $entity->getStatus()->value,
                $entity->getDescription(),
                $entity->getDistanceKm(), $entity->getCo2Saved(),
                (int)$entity->isRecurring(), $entity->getRecurringDays(),
            ]);
            $entity->setId((int)$this->pdo->lastInsertId());
        } else {
            $sql = 'UPDATE trips SET departure_city=?, arrival_city=?, departure_at=?, price_per_seat=?, seats_offered=?, seats_available=?, status=?, description=?, distance_km=?, is_recurring=?, recurring_days=?, updated_at=NOW() WHERE id=?';
            $this->execute($sql, [
                $entity->getDepartureCity(), $entity->getArrivalCity(),
                $entity->getDepartureAt()?->format('Y-m-d H:i:s'),
                $entity->getPricePerSeat(),
                $entity->getSeatsOffered(), $entity->getSeatsAvailable(),
                $entity->getStatus()->value,
                $entity->getDescription(),
                $entity->getDistanceKm(),
                (int)$entity->isRecurring(), $entity->getRecurringDays(),
                $entity->getId(),
            ]);
        }
        return $entity;
    }

    protected function hydrate(array $row): Trip
    {
        $trip = new Trip();
        $trip->setId((int)$row['id']);
        $trip->setDriverId((int)$row['driver_id']);
        $trip->setDepartureCity($row['departure_city']);
        $trip->setArrivalCity($row['arrival_city']);
        $trip->setDepartureAt($row['departure_at'] ?? null);
        $trip->setPricePerSeat((float)($row['price_per_seat'] ?? 0));
        $trip->setSeatsOffered((int)($row['seats_offered'] ?? 1));
        $trip->setSeatsAvailable((int)($row['seats_available'] ?? 1));
        $trip->setStatus($row['status'] ?? 'OPEN');
        $trip->setDescription($row['description'] ?? null);
        $trip->setDistanceKm((float)($row['distance_km'] ?? 0));
        $trip->setCo2Saved((float)($row['co2_saved'] ?? 0));
        $trip->setIsRecurring((bool)($row['is_recurring'] ?? false));
        $trip->setRecurringDays($row['recurring_days'] ?? null);
        $trip->setCreatedAt(!empty($row['created_at']) ? new \DateTimeImmutable($row['created_at']) : null);
        // Driver info (from JOIN)
        if (isset($row['driver_name'])) $trip->setDriverName($row['driver_name']);
        if (isset($row['driver_avatar'])) $trip->setDriverAvatar($row['driver_avatar']);
        if (isset($row['driver_rating'])) $trip->setDriverRating($row['driver_rating'] ? (float)$row['driver_rating'] : null);
        if (isset($row['driver_trust_score'])) $trip->setDriverTrustScore((float)$row['driver_trust_score']);
        return $trip;
    }

    public function findWithDetails(int $id): ?Trip
    {
        $row = $this->fetchOne(
            "SELECT t.*,
                    CONCAT(u.first_name,' ',u.last_name) AS driver_name,
                    u.avatar AS driver_avatar,
                    u.trust_score AS driver_trust_score,
                    ROUND(AVG(r.rating),1) AS driver_rating
             FROM trips t
             JOIN users u ON u.id = t.driver_id
             LEFT JOIN reviews r ON r.target_id = t.driver_id
             WHERE t.id = ?
             GROUP BY t.id",
            [$id]
        );
        if (!$row) return null;
        $trip = $this->hydrate($row);
        $trip->setStops($this->findStops($id));
        return $trip;
    }

    private function findStops(int $tripId): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM stops WHERE trip_id = ? ORDER BY position ASC',
            [$tripId]
        );
        return array_map(function($r) {
            $s = new Stop();
            $s->setId((int)$r['id']);
            $s->setTripId((int)$r['trip_id']);
            $s->setCity($r['city']);
            $s->setPosition((int)$r['position']);
            $s->setExtraPrice((float)($r['extra_price'] ?? 0));
            return $s;
        }, $rows);
    }

    public function saveStops(int $tripId, array $stops): void
    {
        $this->execute('DELETE FROM stops WHERE trip_id = ?', [$tripId]);
        foreach ($stops as $i => $city) {
            if (empty(trim($city))) continue;
            $this->execute(
                'INSERT INTO stops (trip_id, city, position) VALUES (?,?,?)',
                [$tripId, trim($city), $i + 1]
            );
        }
    }

    public function search(array $c, int $page, int $perPage): array
    {
        $where = ["t.status = 'OPEN'", "t.departure_at > NOW()"];
        $params = [];

        if (!empty($c['departure_city'])) {
            $where[] = '(t.departure_city LIKE ? OR EXISTS(SELECT 1 FROM stops s WHERE s.trip_id=t.id AND s.city LIKE ?))';
            $q = '%' . $c['departure_city'] . '%';
            $params[] = $q; $params[] = $q;
        }
        if (!empty($c['arrival_city'])) {
            $where[] = '(t.arrival_city LIKE ? OR EXISTS(SELECT 1 FROM stops s WHERE s.trip_id=t.id AND s.city LIKE ?))';
            $q = '%' . $c['arrival_city'] . '%';
            $params[] = $q; $params[] = $q;
        }
        if (!empty($c['date'])) {
            $where[] = 'DATE(t.departure_at) = ?';
            $params[] = $c['date'];
        }
        if (!empty($c['max_price'])) {
            $where[] = 't.price_per_seat <= ?';
            $params[] = $c['max_price'];
        }
        if (!empty($c['min_seats'])) {
            $where[] = 't.seats_available >= ?';
            $params[] = (int)$c['min_seats'];
        }

        $allowedSort = [
            'price'   => 't.price_per_seat ASC',
            'price_d' => 't.price_per_seat DESC',
            'date'    => 't.departure_at ASC',
            'rating'  => 'driver_rating DESC',
        ];
        $orderBy = $allowedSort[$c['sort'] ?? ''] ?? 't.departure_at ASC';

        $w = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT t.*,
                       CONCAT(u.first_name,' ',u.last_name) AS driver_name,
                       u.avatar AS driver_avatar,
                       u.trust_score AS driver_trust_score,
                       ROUND(AVG(r.rating),1) AS driver_rating
                FROM trips t
                JOIN users u ON u.id = t.driver_id
                LEFT JOIN reviews r ON r.target_id = t.driver_id
                WHERE $w
                GROUP BY t.id
                ORDER BY $orderBy
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function countSearch(array $c): int
    {
        $where = ["t.status = 'OPEN'", "t.departure_at > NOW()"];
        $params = [];
        if (!empty($c['departure_city'])) { $where[] = 't.departure_city LIKE ?'; $params[] = '%'.$c['departure_city'].'%'; }
        if (!empty($c['arrival_city']))   { $where[] = 't.arrival_city LIKE ?';   $params[] = '%'.$c['arrival_city'].'%'; }
        if (!empty($c['date']))           { $where[] = 'DATE(t.departure_at) = ?'; $params[] = $c['date']; }
        if (!empty($c['max_price']))      { $where[] = 't.price_per_seat <= ?';   $params[] = $c['max_price']; }
        if (!empty($c['min_seats']))      { $where[] = 't.seats_available >= ?';  $params[] = (int)$c['min_seats']; }
        $w = implode(' AND ', $where);
        $row = $this->fetchOne("SELECT COUNT(DISTINCT t.id) as c FROM trips t WHERE $w", $params);
        return (int)($row['c'] ?? 0);
    }

    public function decrementSeats(int $tripId, int $n): bool
    {
        $stmt = $this->execute(
            "UPDATE trips SET seats_available = seats_available - ?,
             status = IF(seats_available - ? = 0, 'FULL', status),
             updated_at = NOW()
             WHERE id = ? AND status = 'OPEN' AND seats_available >= ?",
            [$n, $n, $tripId, $n]
        );
        return $stmt->rowCount() === 1;
    }

    public function incrementSeats(int $tripId, int $n): bool
    {
        $stmt = $this->execute(
            "UPDATE trips SET seats_available = seats_available + ?,
             status = IF(status='FULL' AND seats_available + ? > 0, 'OPEN', status),
             updated_at = NOW()
             WHERE id = ?",
            [$n, $n, $tripId]
        );
        return $stmt->rowCount() >= 0;
    }

    public function findByDriver(int $driverId): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM trips WHERE driver_id = ? ORDER BY departure_at DESC',
            [$driverId]
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function findUpcomingByDriver(int $driverId): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM trips WHERE driver_id = ? AND departure_at > NOW() AND status IN ('OPEN','FULL') ORDER BY departure_at ASC LIMIT 10",
            [$driverId]
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function closePastTrips(): int
    {
        $stmt = $this->execute(
            "UPDATE trips SET status = 'COMPLETED', updated_at=NOW()
             WHERE status IN ('OPEN','FULL') AND departure_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)"
        );
        return $stmt->rowCount();
    }

    public function findPopularRoutes(int $limit = 10): array
    {
        return $this->fetchAll(
            "SELECT departure_city, arrival_city, COUNT(*) as trip_count, AVG(price_per_seat) as avg_price
             FROM trips GROUP BY departure_city, arrival_city
             ORDER BY trip_count DESC LIMIT ?",
            [$limit]
        );
    }

    public function countAll(): int
    {
        return (int)($this->fetchOne('SELECT COUNT(*) as c FROM trips')['c'] ?? 0);
    }

    public function getMonthlyStats(): array
    {
        return $this->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
             FROM trips WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month ORDER BY month"
        );
    }

    public function getOccupancyRate(): float
    {
        $row = $this->fetchOne(
            "SELECT ROUND(100 * SUM(seats_offered - seats_available) / NULLIF(SUM(seats_offered),0), 1) AS rate
             FROM trips WHERE status IN ('OPEN','FULL','COMPLETED')"
        );
        return (float)($row['rate'] ?? 0);
    }
}
