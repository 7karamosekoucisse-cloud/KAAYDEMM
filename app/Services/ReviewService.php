<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\{User, Review};
use App\Enums\BookingStatus;
use App\Interfaces\{BookingRepositoryInterface, TripRepositoryInterface};
use App\Repositories\ReviewRepository;
use App\Exceptions\{AuthorizationException, EvaluationException, NotFoundException};
use App\Helpers\Validator;

class ReviewService
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepo,
        private readonly ReviewRepository           $reviewRepo,
        private readonly TripRepositoryInterface    $tripRepo
    ) {}

    public function rate(User $author, int $bookingId, array $data): Review
    {
        $booking = $this->bookingRepo->findWithDetails($bookingId);
        if (!$booking) throw new NotFoundException('Réservation introuvable');

        if ($booking->getStatus() !== BookingStatus::COMPLETED) {
            throw new EvaluationException('Le trajet doit être terminé pour évaluer.');
        }

        // Vérification d'autorisation : seul le passager ou le conducteur peut évaluer
        $trip = $this->tripRepo->find($booking->getTripId());
        if (!$trip) throw new NotFoundException('Trajet introuvable');

        $isPassenger = $booking->getPassengerId() === $author->getId();
        $isDriver    = $trip->getDriverId() === $author->getId();
        if (!$isPassenger && !$isDriver) {
            throw new AuthorizationException('Vous n\'êtes pas autorisé à évaluer cette réservation.');
        }

        if ($this->reviewRepo->existsByAuthorAndBooking($author->getId(), $bookingId)) {
            throw new EvaluationException('Vous avez déjà évalué cette réservation.');
        }

        Validator::make()
            ->required($data, ['rating'])
            ->between($data, 'rating', 1, 5)
            ->throwIfErrors();

        // Si l'auteur est le passager → cible = conducteur, et inversement
        $targetId = $isPassenger
            ? $trip->getDriverId()
            : $booking->getPassengerId();

        $review = new Review();
        $review->setBookingId($bookingId);
        $review->setAuthorId($author->getId());
        $review->setTargetId($targetId);
        $review->setRating((int)$data['rating']);
        $review->setComment($data['comment'] ?? null);
        $review->setIsPunctual((bool)($data['is_punctual'] ?? true));
        $review->setIsClean((bool)($data['is_clean'] ?? true));
        $review->setIsFriendly((bool)($data['is_friendly'] ?? true));
        $review->validate();

        $this->reviewRepo->save($review);
        return $review;
    }
}
