<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\{Report, AbstractEntity};

class ReportRepository extends AbstractRepository
{
    protected string $table = 'reports';

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var Report $entity */
        if ($entity->isNew()) {
            $this->execute(
                'INSERT INTO reports (reporter_id, target_id, target_type, reason, details, status, created_at) VALUES (?,?,?,?,?,?,NOW())',
                [$entity->getReporterId(), $entity->getTargetId(), $entity->getTargetType(), $entity->getReason(), $entity->getDetails(), $entity->getStatus()]
            );
            $entity->setId((int)$this->pdo->lastInsertId());
        } else {
            $this->execute(
                'UPDATE reports SET status=?, resolved_by=?, resolution=? WHERE id=?',
                [$entity->getStatus(), $entity->getResolvedBy(), $entity->getResolution(), $entity->getId()]
            );
        }
        return $entity;
    }

    protected function hydrate(array $row): Report
    {
        $r = new Report();
        $r->setId((int)$row['id']);
        $r->setReporterId((int)$row['reporter_id']);
        $r->setTargetId((int)$row['target_id']);
        $r->setTargetType($row['target_type']);
        $r->setReason($row['reason']);
        $r->setDetails($row['details'] ?? null);
        $r->setStatus($row['status']);
        $r->setResolvedBy($row['resolved_by'] ? (int)$row['resolved_by'] : null);
        $r->setResolution($row['resolution'] ?? null);
        $r->setCreatedAt(!empty($row['created_at']) ? new \DateTimeImmutable($row['created_at']) : null);
        if (isset($row['reporter_name'])) $r->setReporterName($row['reporter_name']);
        if (isset($row['target_name']))   $r->setTargetName($row['target_name']);
        return $r;
    }

    public function findPending(): array
    {
        $rows = $this->fetchAll(
            "SELECT r.*, CONCAT(u.first_name,' ',u.last_name) AS reporter_name
             FROM reports r JOIN users u ON u.id=r.reporter_id
             WHERE r.status='PENDING' ORDER BY r.created_at DESC"
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function countPending(): int
    {
        return (int)($this->fetchOne("SELECT COUNT(*) as c FROM reports WHERE status='PENDING'")['c'] ?? 0);
    }
}
