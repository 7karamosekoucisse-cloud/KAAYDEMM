<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\Request;
use Core\Response;
use App\Services\{TripService, BookingService};
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface, TripRepositoryInterface, BookingRepositoryInterface};
use App\Exceptions\{ValidationException, AuthorizationException, BookingException};
use App\Helpers\Csrf;
use Core\Session;

class TripController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly TripRepositoryInterface    $tripRepo,
        private readonly TripService                $tripService,
        private readonly BookingService             $bookingService,
        private readonly BookingRepositoryInterface $bookingRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function index(Request $req): Response
    {
        $user  = $this->auth->currentUser();
        $trips = $user ? $this->tripRepo->findByDriver($user->getId()) : [];
        return $this->render('trips.index', ['title' => 'Mes trajets', 'trips' => $trips]);
    }

    public function show(Request $req, string $id): Response
    {
        $trip = $this->tripRepo->findWithDetails((int)$id);
        if (!$trip) return $this->render('errors.404', ['title' => 'Trajet introuvable']);
        $bookings = [];
        $user = $this->auth->currentUser();
        if ($user && $trip->getDriverId() === $user->getId()) {
            $bookings = $this->bookingRepo->findByTrip((int)$id);
        }
        return $this->render('trips.show', [
            'title'    => $trip->getDepartureCity() . ' → ' . $trip->getArrivalCity(),
            'trip'     => $trip,
            'bookings' => $bookings,
        ]);
    }

    public function create(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(\App\Enums\Role::DRIVER);
        $user = $this->auth->currentUser();
        return $this->render('trips.create', [
            'title'   => 'Publier un trajet',
            'profile' => $user->getDriverProfile(),
        ]);
    }

    public function store(Request $req): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user = $this->auth->currentUser();
            $data = $req->all();
            // Stops: comma-separated or array
            $stopsRaw = $req->post('stops', '');
            $data['stops'] = is_array($stopsRaw)
                ? $stopsRaw
                : array_filter(array_map('trim', explode(',', $stopsRaw)));
            $trip = $this->tripService->publish($user, $data);
            return $this->redirect('/trips/' . $trip->getId(), 'Trajet publié avec succès !');
        } catch (ValidationException $e) {
            return $this->render('trips.create', [
                'title'  => 'Publier un trajet',
                'errors' => $e->getErrors(),
                'old'    => $req->all(),
            ]);
        } catch (AuthorizationException $e) {
            return $this->redirect('/dashboard', $e->getMessage(), 'error');
        }
    }

    public function edit(Request $req, string $id): Response
    {
        $this->requireAuth();
        $trip = $this->tripRepo->findWithDetails((int)$id);
        if (!$trip) return $this->render('errors.404', ['title' => 'Introuvable']);
        $user = $this->auth->currentUser();
        if ($trip->getDriverId() !== $user->getId()) return $this->redirect('/dashboard', 'Accès refusé', 'error');
        return $this->render('trips.edit', ['title' => 'Modifier le trajet', 'trip' => $trip]);
    }

    public function update(Request $req, string $id): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user = $this->auth->currentUser();
            $trip = $this->tripService->update($user, (int)$id, $req->all());
            return $this->redirect('/trips/' . $id, 'Trajet modifié.');
        } catch (ValidationException | BookingException $e) {
            Session::flash('error', $e->getMessage());
            return $this->redirect('/trips/' . $id . '/edit');
        }
    }

    public function cancel(Request $req, string $id): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user = $this->auth->currentUser();
            $this->tripService->cancel($user, (int)$id, $req->post('reason', ''));
            return $this->redirect('/trips', 'Trajet annulé.');
        } catch (\Throwable $e) {
            return $this->redirect('/trips/' . $id, $e->getMessage(), 'error');
        }
    }
    public function complete(Request $req, string $id): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user = $this->auth->currentUser();
            $this->tripService->complete($user, (int)$id);
            return $this->redirect('/trips/' . $id, 'Trajet marque comme termine. Les passagers peuvent maintenant laisser un avis.');
        } catch (\Throwable $e) {
            return $this->redirect('/trips/' . $id, $e->getMessage(), 'error');
        }
    }

    public function updatePosition(Request $req, string $id): Response
    {
        $user = $this->auth->currentUser();
        $trip = $this->tripRepo->find((int)$id);
        if (!$trip || $trip->getDriverId() !== $user->getId()) {
            return new Response(json_encode(['error' => 'Unauthorized']), 403, ['Content-Type' => 'application/json']);
        }
        if (!in_array($trip->getStatus()->value, ['OPEN', 'FULL'])) {
            return new Response(json_encode(['error' => 'Trip not active']), 400, ['Content-Type' => 'application/json']);
        }
        $lat = (float)($req->post('lat') ?? 0);
        $lng = (float)($req->post('lng') ?? 0);
        if (!$lat || !$lng) {
            return new Response(json_encode(['error' => 'Coords invalides']), 400, ['Content-Type' => 'application/json']);
        }
        $dir = dirname(__DIR__, 2) . '/storage/locations';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        file_put_contents($dir . '/' . (int)$id . '.json', json_encode([
            'lat' => $lat,
            'lng' => $lng,
            'ts'  => time(),
        ]));
        return new Response(json_encode(['ok' => true]), 200, ['Content-Type' => 'application/json']);
    }

    public function scan(Request $req, string $id): Response
    {
        $this->requireAuth();
        $user = $this->auth->currentUser();
        $trip = $this->tripRepo->find((int)$id);
        if (!$trip || $trip->getDriverId() !== $user->getId()) {
            return $this->redirect('/dashboard', 'Acces refuse.', 'error');
        }
        return $this->render('trips.scan', [
            'title' => 'Scanner les billets',
            'trip'  => $trip,
        ]);
    }

}
