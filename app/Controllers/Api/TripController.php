<?php
declare(strict_types=1);
namespace App\Controllers\Api;

use Core\{Request, Response};
use App\Controllers\AbstractController;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface, TripRepositoryInterface};

class TripController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly TripRepositoryInterface $tripRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function index(Request $req): Response
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
        $perPage = min(50, max(1, (int)$req->get('per_page', 10)));
        $trips   = $this->tripRepo->search($criteria, $page, $perPage);
        $total   = $this->tripRepo->countSearch($criteria);
        return $this->json([
            'data' => array_map(fn($t) => [
                'id'              => $t->getId(),
                'departure_city'  => $t->getDepartureCity(),
                'arrival_city'    => $t->getArrivalCity(),
                'departure_at'    => $t->getDepartureAt()?->format(\DateTimeInterface::ATOM),
                'price_per_seat'  => $t->getPricePerSeat(),
                'seats_available' => $t->getSeatsAvailable(),
                'status'          => $t->getStatus()->value,
                'driver'          => ['name' => $t->getDriverName(), 'rating' => $t->getDriverRating()],
                'co2_saved'       => $t->getCo2Saved(),
            ], $trips),
            'meta' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => (int)ceil($total / $perPage)],
        ]);
    }

    public function show(Request $req, string $id): Response
    {
        $trip = $this->tripRepo->findWithDetails((int)$id);
        if (!$trip) return $this->json(['error' => 'Trip not found'], 404);
        return $this->json([
            'id'              => $trip->getId(),
            'departure_city'  => $trip->getDepartureCity(),
            'arrival_city'    => $trip->getArrivalCity(),
            'departure_at'    => $trip->getDepartureAt()?->format(\DateTimeInterface::ATOM),
            'price_per_seat'  => $trip->getPricePerSeat(),
            'seats_offered'   => $trip->getSeatsOffered(),
            'seats_available' => $trip->getSeatsAvailable(),
            'status'          => $trip->getStatus()->value,
            'description'     => $trip->getDescription(),
            'distance_km'     => $trip->getDistanceKm(),
            'co2_saved'       => $trip->getCo2Saved(),
            'stops'           => array_map(fn($s) => $s->getCity(), $trip->getStops()),
            'driver' => [
                'name'        => $trip->getDriverName(),
                'rating'      => $trip->getDriverRating(),
                'trust_score' => $trip->getDriverTrustScore(),
            ],
        ]);
    }

    public function popular(Request $req): Response
    {
        $routes = $this->tripRepo->findPopularRoutes(10);
        return $this->json(['data' => $routes]);
    }

    /**
     * GET /api/distances — distances entre villes sénégalaises
     */
    public function distances(Request $req): Response
    {
        $data = [
            ['from'=>'Dakar',       'to'=>'Thiès',       'km'=>70,  'price_suggested'=>1100],
            ['from'=>'Dakar',       'to'=>'Saint-Louis', 'km'=>270, 'price_suggested'=>4100],
            ['from'=>'Dakar',       'to'=>'Kaolack',     'km'=>195, 'price_suggested'=>2900],
            ['from'=>'Dakar',       'to'=>'Mbour',       'km'=>80,  'price_suggested'=>1200],
            ['from'=>'Dakar',       'to'=>'Ziguinchor',  'km'=>470, 'price_suggested'=>7100],
            ['from'=>'Dakar',       'to'=>'Touba',       'km'=>195, 'price_suggested'=>2900],
            ['from'=>'Dakar',       'to'=>'Diourbel',    'km'=>145, 'price_suggested'=>2200],
            ['from'=>'Dakar',       'to'=>'Tambacounda', 'km'=>480, 'price_suggested'=>7200],
            ['from'=>'Dakar',       'to'=>'Louga',       'km'=>210, 'price_suggested'=>3200],
            ['from'=>'Thiès',       'to'=>'Saint-Louis', 'km'=>230, 'price_suggested'=>3500],
            ['from'=>'Thiès',       'to'=>'Kaolack',     'km'=>130, 'price_suggested'=>2000],
            ['from'=>'Kaolack',     'to'=>'Ziguinchor',  'km'=>290, 'price_suggested'=>4400],
            ['from'=>'Kaolack',     'to'=>'Tambacounda', 'km'=>290, 'price_suggested'=>4400],
            ['from'=>'Mbour',       'to'=>'Thiès',       'km'=>50,  'price_suggested'=>800],
            ['from'=>'Ziguinchor',  'to'=>'Kolda',       'km'=>110, 'price_suggested'=>1700],
        ];

        // Filtrer si from/to fournis
        $from = $req->get('from','');
        $to   = $req->get('to','');
        if ($from && $to) {
            $normalize = fn($s) => mb_strtolower(trim($s));
            $fn = $normalize($from); $tn = $normalize($to);
            $data = array_values(array_filter($data, function($r) use ($fn,$tn,$normalize) {
                $rf = $normalize($r['from']); $rt = $normalize($r['to']);
                return ($rf===$fn && $rt===$tn) || ($rf===$tn && $rt===$fn);
            }));
        }
        return $this->json(['data' => $data, 'price_per_km' => 15]);
    }

    /**
     * GET /api/trips/{id}/position - live driver position (TTL 30s)
     */
    public function position(Request $req, string $id): Response
    {
        $file = dirname(__DIR__, 3) . '/storage/locations/' . (int)$id . '.json';
        if (!file_exists($file) || (time() - filemtime($file)) > 30) {
            return $this->json(['active' => false]);
        }
        $pos = json_decode(file_get_contents($file), true);
        $pos['active'] = true;
        return $this->json($pos);
    }

}
