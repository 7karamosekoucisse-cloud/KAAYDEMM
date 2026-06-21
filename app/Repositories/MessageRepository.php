<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\{Message, AbstractEntity};

class MessageRepository extends AbstractRepository
{
    protected string $table = 'messages';

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var Message $entity */
        $this->execute(
            'INSERT INTO messages (sender_id, recipient_id, content, booking_id, created_at) VALUES (?,?,?,?,NOW())',
            [$entity->getSenderId(), $entity->getRecipientId(), $entity->getContent(), $entity->getBookingId()]
        );
        $entity->setId((int)$this->pdo->lastInsertId());
        return $entity;
    }

    protected function hydrate(array $row): Message
    {
        $m = new Message();
        $m->setId((int)$row['id']);
        $m->setSenderId((int)$row['sender_id']);
        $m->setRecipientId((int)$row['recipient_id']);
        $m->setContent($row['content']);
        $m->setIsRead((bool)($row['is_read'] ?? false));
        $m->setBookingId($row['booking_id'] ? (int)$row['booking_id'] : null);
        $m->setCreatedAt(!empty($row['created_at']) ? new \DateTimeImmutable($row['created_at']) : null);
        if (isset($row['sender_name'])) $m->setSenderName($row['sender_name']);
        return $m;
    }

    public function getConversation(int $userId, int $otherId): array
    {
        $rows = $this->fetchAll(
            "SELECT m.*, CONCAT(u.first_name,' ',u.last_name) AS sender_name, u.avatar AS sender_avatar
             FROM messages m JOIN users u ON u.id = m.sender_id
             WHERE (m.sender_id=? AND m.recipient_id=?) OR (m.sender_id=? AND m.recipient_id=?)
             ORDER BY m.created_at ASC",
            [$userId, $otherId, $otherId, $userId]
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function getInbox(int $userId): array
    {
        return $this->fetchAll(
            "SELECT m.*, CONCAT(u.first_name,' ',u.last_name) AS sender_name, u.avatar AS sender_avatar,
                    (SELECT COUNT(*) FROM messages m2 WHERE m2.sender_id=m.sender_id AND m2.recipient_id=? AND m2.is_read=0) AS unread_count
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.id = (SELECT MAX(m2.id) FROM messages m2 WHERE (m2.sender_id=m.sender_id AND m2.recipient_id=?) OR (m2.sender_id=? AND m2.recipient_id=m.sender_id))
             AND (m.sender_id=? OR m.recipient_id=?)
             ORDER BY m.created_at DESC",
            [$userId, $userId, $userId, $userId, $userId]
        );
    }

    public function markAsRead(int $senderId, int $recipientId): void
    {
        $this->execute(
            'UPDATE messages SET is_read=1 WHERE sender_id=? AND recipient_id=? AND is_read=0',
            [$senderId, $recipientId]
        );
    }

    public function countUnread(int $userId): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) as c FROM messages WHERE recipient_id=? AND is_read=0', [$userId]);
        return (int)($row['c'] ?? 0);
    }
}
