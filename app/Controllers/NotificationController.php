<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response};
use App\Repositories\NotificationRepository;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface};

class NotificationController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly NotificationRepository $notifRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function index(Request $req): Response
    {
        $this->requireAuth();
        $user  = $this->auth->currentUser();
        $notifs = $this->notifRepo->findByUser($user->getId(), 50);
        $this->notifRepo->markAllRead($user->getId());
        if ($req->wantsJson()) {
            return $this->json(array_map(fn($n) => [
                'id'    => $n->getId(),
                'type'  => $n->getType(),
                'title' => $n->getTitle(),
                'body'  => $n->getBody(),
                'link'  => $n->getLink(),
                'read'  => $n->isRead(),
                'time'  => $n->getCreatedAt()?->format('d/m H:i'),
            ], $notifs));
        }
        return $this->render('profile.notifications', [
            'title'  => 'Notifications',
            'notifs' => $notifs,
        ]);
    }
}
