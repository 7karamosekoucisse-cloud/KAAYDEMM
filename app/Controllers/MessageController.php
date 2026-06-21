<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response};
use App\Services\EmailService;
use App\Models\Message;
use App\Repositories\MessageRepository;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface};
use App\Helpers\Csrf;

class MessageController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly MessageRepository $msgRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function inbox(Request $req): Response
    {
        $this->requireAuth();
        $user = $this->auth->currentUser();
        $inbox = $this->msgRepo->getInbox($user->getId());
        return $this->render('profile.inbox', [
            'title' => 'Messagerie',
            'inbox' => $inbox,
        ]);
    }

    public function conversation(Request $req, string $otherId): Response
    {
        $this->requireAuth();
        $user     = $this->auth->currentUser();
        $other    = $this->userRepo->find((int)$otherId);
        if (!$other) return $this->redirect('/messages', 'Utilisateur introuvable', 'error');

        $messages = $this->msgRepo->getConversation($user->getId(), (int)$otherId);
        $this->msgRepo->markAsRead((int)$otherId, $user->getId());

        if ($req->wantsJson()) {
            return $this->json(array_map(fn($m) => [
                'id'          => $m->getId(),
                'sender_id'   => $m->getSenderId(),
                'content'     => $m->getContent(),
                'is_read'     => $m->isRead(),
                'created_at'  => $m->getCreatedAt()?->format('H:i'),
                'sender_name' => $m->getSenderName(),
            ], $messages));
        }

        return $this->render('profile.conversation', [
            'title'    => 'Conversation avec ' . $other->getFullName(),
            'messages' => $messages,
            'other'    => $other,
        ]);
    }

    public function send(Request $req, string $otherId): Response
    {
        $this->requireAuth();
        Csrf::verify($req->all());
        $user    = $this->auth->currentUser();
        $content = trim($req->post('content', ''));
        if (!$content || strlen($content) > 1000) {
            return $this->json(['error' => 'Message invalide'], 422);
        }
        $msg = new Message();
        $msg->setSenderId($user->getId());
        $msg->setRecipientId((int)$otherId);
        $msg->setContent($content);
        $this->msgRepo->save($msg);

        // Email notification au destinataire (async)
        try {
            $recipient = $this->userRepo->find((int)$otherId);
            if ($recipient && $recipient->getEmail()) {
                $preview = strlen($content) > 100 ? substr($content, 0, 97) . '...' : $content;
                (new EmailService())->sendNewMessage(
                    $recipient->getEmail(),
                    $recipient->getFirstName(),
                    $user->getFullName(),
                    $preview
                );
            }
        } catch (\Throwable) {}

        if ($req->wantsJson()) {
            return $this->json([
                'id'         => $msg->getId(),
                'content'    => $msg->getContent(),
                'created_at' => (new \DateTimeImmutable())->format('H:i'),
            ]);
        }
        return $this->redirect('/messages/' . $otherId);
    }
}
// Note: sendEmailNotification is called after message save in send()
