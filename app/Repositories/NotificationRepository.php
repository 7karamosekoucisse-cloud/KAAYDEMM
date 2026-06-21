<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\{Notification, AbstractEntity};

class NotificationRepository extends AbstractRepository
{
    protected string $table = 'notifications';

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var Notification $entity */
        $this->execute(
            'INSERT INTO notifications (user_id, type, title, body, link, payload, created_at) VALUES (?,?,?,?,?,?,NOW())',
            [
                $entity->getUserId(), $entity->getType(), $entity->getTitle(),
                $entity->getBody(), $entity->getLink(),
                json_encode($entity->getPayload()),
            ]
        );
        $entity->setId((int)$this->pdo->lastInsertId());
        return $entity;
    }

    protected function hydrate(array $row): Notification
    {
        $n = new Notification();
        $n->setId((int)$row['id']);
        $n->setUserId((int)$row['user_id']);
        $n->setType($row['type']);
        $n->setTitle($row['title']);
        $n->setBody($row['body']);
        $n->setLink($row['link'] ?? null);
        $n->setPayload(json_decode($row['payload'] ?? '[]', true) ?? []);
        $n->setIsRead((bool)($row['is_read'] ?? false));
        $n->setCreatedAt(!empty($row['created_at']) ? new \DateTimeImmutable($row['created_at']) : null);
        return $n;
    }

    public function findByUser(int $userId, int $limit = 20): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT ?',
            [$userId, $limit]
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function markAllRead(int $userId): void
    {
        $this->execute('UPDATE notifications SET is_read=1 WHERE user_id=?', [$userId]);
    }

    public function countUnread(int $userId): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) as c FROM notifications WHERE user_id=? AND is_read=0', [$userId]);
        return (int)($row['c'] ?? 0);
    }
}
