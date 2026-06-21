<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\{User, Notification};
use App\Interfaces\NotifierInterface;
use App\Repositories\NotificationRepository;

class NotificationService implements NotifierInterface
{
    public function __construct(
        private readonly NotificationRepository $notifRepo
    ) {}

    public function notify(
        User $user,
        string $type,
        string $title,
        string $body,
        array $payload = [],
        ?string $link = null
    ): void {
        $n = new Notification();
        $n->setUserId($user->getId());
        $n->setType($type);
        $n->setTitle($title);
        $n->setBody($body);
        $n->setLink($link);
        $n->setPayload($payload);
        $this->notifRepo->save($n);
    }

    public function notifyById(
        int $userId,
        string $type,
        string $title,
        string $body,
        ?string $link = null
    ): void {
        $n = new Notification();
        $n->setUserId($userId);
        $n->setType($type);
        $n->setTitle($title);
        $n->setBody($body);
        $n->setLink($link);
        $this->notifRepo->save($n);
    }
}
