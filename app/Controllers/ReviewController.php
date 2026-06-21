<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response, Session};
use App\Services\ReviewService;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface};
use App\Exceptions\{EvaluationException, ValidationException};
use App\Helpers\Csrf;

class ReviewController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly ReviewService $reviewService
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function store(Request $req, string $bookingId): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user   = $this->auth->currentUser();
            $review = $this->reviewService->rate($user, (int)$bookingId, $req->all());
            return $this->redirect('/bookings/' . $bookingId, 'Merci pour votre évaluation !');
        } catch (EvaluationException | ValidationException $e) {
            return $this->redirect('/bookings/' . $bookingId, $e->getMessage(), 'error');
        }
    }
}
