<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\Request;
use Core\Response;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface, TripRepositoryInterface};

class SearchController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly TripRepositoryInterface $tripRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function home(Request $req): Response
    {
        $popular = $this->tripRepo->findPopularRoutes(4);
        return $this->render('search.home', [
            'title'   => 'Kaay Dem ! — Covoiturage étudiant',
            'popular' => $popular,
        ], 'layouts.main');
    }

    public function search(Request $req): Response
    {
        $criteria = [
            'departure_city' => $req->get('from', ''),
            'arrival_city'   => $req->get('to', ''),
            'date'           => $req->get('date', ''),
            'max_price'      => $req->get('max_price', ''),
            'min_seats'      => $req->get('seats', 1),
            'sort'           => $req->get('sort', 'date'),
        ];

        $page    = max(1, (int)$req->get('page', 1));
        $perPage = 10;

        $trips = $this->tripRepo->search($criteria, $page, $perPage);
        $total = $this->tripRepo->countSearch($criteria);
        $pages = (int)ceil($total / $perPage);

        if ($req->wantsJson()) {
            return $this->json([
                'trips'   => array_map(fn($t) => $this->tripToArray($t), $trips),
                'total'   => $total,
                'page'    => $page,
                'pages'   => $pages,
            ]);
        }

        return $this->render('search.results', [
            'title'    => 'Résultats de recherche',
            'trips'    => $trips,
            'criteria' => $criteria,
            'total'    => $total,
            'page'     => $page,
            'pages'    => $pages,
        ]);
    }

    private function tripToArray(\App\Models\Trip $t): array
    {
        return [
            'id'             => $t->getId(),
            'departure_city' => $t->getDepartureCity(),
            'arrival_city'   => $t->getArrivalCity(),
            'departure_at'   => $t->getDepartureAt()?->format('Y-m-d H:i'),
            'price_per_seat' => $t->getPricePerSeat(),
            'seats_available'=> $t->getSeatsAvailable(),
            'driver_name'    => $t->getDriverName(),
            'driver_rating'  => $t->getDriverRating(),
        ];
    }
}
