<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response, Session};
use App\Services\BookingService;
use App\Repositories\BookingRepository;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface, TripRepositoryInterface};
use App\Exceptions\{ValidationException, BookingException};
use App\Helpers\Csrf;

class BookingController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly BookingService         $bookingService,
        private readonly BookingRepository      $bookingRepo,
        private readonly TripRepositoryInterface $tripRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function book(Request $req, string $tripId): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user    = $this->auth->currentUser();
            $seats   = max(1, (int)$req->post('seats', 1));
            $booking = $this->bookingService->book($user, (int)$tripId, $seats);
            return $this->redirect('/bookings/' . $booking->getId(), 'Réservation créée ! En attente de confirmation du conducteur.');
        } catch (BookingException $e) {
            Session::flash('error', $e->getMessage());
            return $this->redirect('/trips/' . $tripId);
        }
    }

    public function show(Request $req, string $id): Response
    {
        $this->requireAuth();
        $booking = $this->bookingRepo->findWithDetails((int)$id);
        if (!$booking) return $this->render('errors.404', ['title' => 'Introuvable']);
        $user = $this->auth->currentUser();
        // Authorization: passenger or driver of the trip
        $trip = $this->tripRepo->find($booking->getTripId());
        if ($booking->getPassengerId() !== $user->getId() &&
            ($trip && $trip->getDriverId() !== $user->getId()) &&
            !$user->hasApprovedRole(\App\Enums\Role::ADMIN)) {
            return $this->redirect('/dashboard', 'Accès refusé', 'error');
        }
        return $this->render('bookings.show', [
            'title'   => 'Réservation #' . $id,
            'booking' => $booking,
            'trip'    => $trip,
        ]);
    }

    public function confirm(Request $req, string $id): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user    = $this->auth->currentUser();
            $booking = $this->bookingService->confirm($user, (int)$id);
            return $this->redirect('/bookings/' . $id, 'Réservation confirmée !');
        } catch (\Throwable $e) {
            return $this->redirect('/bookings/' . $id, $e->getMessage(), 'error');
        }
    }

    public function cancel(Request $req, string $id): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user   = $this->auth->currentUser();
            $reason = $req->post('reason', '');
            $this->bookingService->cancel($user, (int)$id, $reason);
            return $this->redirect('/dashboard', 'Réservation annulée.');
        } catch (\Throwable $e) {
            return $this->redirect('/bookings/' . $id, $e->getMessage(), 'error');
        }
    }

    public function checkIn(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(\App\Enums\Role::DRIVER);
        try {
            Csrf::verify($req->all());
            $user    = $this->auth->currentUser();
            $qrCode  = $req->post('qr_code', '');
            $booking = $this->bookingService->checkIn($user, $qrCode);
            if ($req->wantsJson()) {
                return $this->json(['success' => true, 'passenger' => $booking->getPassengerName()]);
            }
            return $this->redirect('/dashboard', 'Passager enregistré : ' . $booking->getPassengerName());
        } catch (\Throwable $e) {
            if ($req->wantsJson()) {
                return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return $this->redirect('/dashboard', $e->getMessage(), 'error');
        }
    }

    public function myBookings(Request $req): Response
    {
        $this->requireAuth();
        $user     = $this->auth->currentUser();
        $status   = $req->get('status');
        $bookings = $this->bookingRepo->findByPassenger($user->getId(), $status ?: null);
        return $this->render('bookings.index', [
            'title'    => 'Mes réservations',
            'bookings' => $bookings,
            'status'   => $status,
        ]);
    }
}
