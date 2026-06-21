<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\{WaitlistEntry, AbstractEntity};

class WaitlistRepository extends AbstractRepository
{
    protected string $table = 'waitlist';

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var WaitlistEntry $entity */
        $pos = $this->getNextPosition($entity->getTripId());
        $this->execute(
            'INSERT INTO waitlist (trip_id, passenger_id, seats_wanted, position, created_at) VALUES (?,?,?,?,NOW())',
            [$entity->getTripId(), $entity->getPassengerId(), $entity->getSeatsWanted(), $pos]
        );
        $entity->setId((int)$this->pdo->lastInsertId());
        $entity->setPosition($pos);
        return $entity;
    }

    protected function hydrate(array $row): WaitlistEntry
    {
        $w = new WaitlistEntry();
        $w->setId((int)$row['id']);
        $w->setTripId((int)$row['trip_id']);
        $w->setPassengerId((int)$row['passenger_id']);
        $w->setSeatsWanted((int)($row['seats_wanted'] ?? 1));
        $w->setPosition((int)($row['position'] ?? 0));
        if (isset($row['passenger_name'])) $w->setPassengerName($row['passenger_name']);
        return $w;
    }

    private function getNextPosition(int $tripId): int
    {
        $row = $this->fetchOne('SELECT MAX(position) as m FROM waitlist WHERE trip_id=?', [$tripId]);
        return (int)($row['m'] ?? 0) + 1;
    }

    public function findByTrip(int $tripId): array
    {
        $rows = $this->fetchAll(
            "SELECT w.*, CONCAT(u.first_name,' ',u.last_name) AS passenger_name
             FROM waitlist w JOIN users u ON u.id=w.passenger_id
             WHERE w.trip_id=? ORDER BY w.position ASC",
            [$tripId]
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function removeEntry(int $tripId, int $passengerId): void
    {
        $this->execute('DELETE FROM waitlist WHERE trip_id=? AND passenger_id=?', [$tripId, $passengerId]);
    }

    public function existsEntry(int $tripId, int $passengerId): bool
    {
        $row = $this->fetchOne('SELECT id FROM waitlist WHERE trip_id=? AND passenger_id=?', [$tripId, $passengerId]);
        return $row !== null;
    }
}
