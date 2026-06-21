<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\{Review, AbstractEntity};

class ReviewRepository extends AbstractRepository
{
    protected string $table      = 'reviews';
    protected string $modelClass = Review::class;

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var Review $entity */
        $this->execute(
            'INSERT INTO reviews (booking_id, author_id, target_id, rating, comment, is_punctual, is_clean, is_friendly, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())',
            [
                $entity->getBookingId(), $entity->getAuthorId(), $entity->getTargetId(),
                $entity->getRating(), $entity->getComment(),
                (int)$entity->isPunctual(), (int)$entity->isClean(), (int)$entity->isFriendly(),
            ]
        );
        $entity->setId((int)$this->pdo->lastInsertId());
        return $entity;
    }

    protected function hydrate(array $row): Review
    {
        $r = new Review();
        $r->setId((int)$row['id']);
        $r->setBookingId((int)$row['booking_id']);
        $r->setAuthorId((int)$row['author_id']);
        $r->setTargetId((int)$row['target_id']);
        $r->setRating((int)$row['rating']);
        $r->setComment($row['comment'] ?? null);
        $r->setIsPunctual((bool)($row['is_punctual'] ?? true));
        $r->setIsClean((bool)($row['is_clean'] ?? true));
        $r->setIsFriendly((bool)($row['is_friendly'] ?? true));
        $r->setCreatedAt(!empty($row['created_at']) ? new \DateTimeImmutable($row['created_at']) : null);
        if (isset($row['author_name'])) $r->setAuthorName($row['author_name']);
        if (isset($row['author_avatar'])) $r->setAuthorAvatar($row['author_avatar']);
        return $r;
    }

    public function existsByAuthorAndBooking(int $authorId, int $bookingId): bool
    {
        $row = $this->fetchOne(
            'SELECT id FROM reviews WHERE author_id=? AND booking_id=?',
            [$authorId, $bookingId]
        );
        return $row !== null;
    }

    public function findByTarget(int $targetId): array
    {
        $rows = $this->fetchAll(
            "SELECT r.*, CONCAT(u.first_name,' ',u.last_name) AS author_name, u.avatar AS author_avatar
             FROM reviews r JOIN users u ON u.id = r.author_id
             WHERE r.target_id = ? ORDER BY r.created_at DESC",
            [$targetId]
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function getAverageRating(int $targetId): ?float
    {
        $row = $this->fetchOne(
            'SELECT ROUND(AVG(rating),1) AS avg, COUNT(*) AS cnt FROM reviews WHERE target_id=?',
            [$targetId]
        );
        return $row && $row['cnt'] > 0 ? (float)$row['avg'] : null;
    }

    public function getDetailedStats(int $targetId): array
    {
        $row = $this->fetchOne(
            "SELECT ROUND(AVG(rating),2) AS avg_rating,
                    ROUND(AVG(is_punctual)*100,0) AS pct_punctual,
                    ROUND(AVG(is_clean)*100,0) AS pct_clean,
                    ROUND(AVG(is_friendly)*100,0) AS pct_friendly,
                    COUNT(*) AS total
             FROM reviews WHERE target_id=?",
            [$targetId]
        );
        return $row ?? [];
    }
}
