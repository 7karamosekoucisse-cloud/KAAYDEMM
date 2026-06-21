<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response};
use App\Services\StatisticsService;
use App\Repositories\{BookingRepository, NotificationRepository};
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface, TripRepositoryInterface};
use App\Enums\Role;

class DashboardController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly TripRepositoryInterface $tripRepo,
        private readonly BookingRepository       $bookingRepo,
        private readonly NotificationRepository  $notifRepo,
        private readonly StatisticsService       $stats
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function index(Request $req): Response
    {
        $this->requireAuth();
        $user = $this->auth->currentUser();

        $notifications = $this->notifRepo->findByUser($user->getId(), 5);
        $unreadNotif   = $this->notifRepo->countUnread($user->getId());

        if ($user->hasApprovedRole(Role::ADMIN)) {
            $data = $this->stats->getAdminDashboard();
            return $this->render('dashboard.admin', array_merge($data, [
                'title'        => 'Tableau de bord Administrateur',
                'notifications'=> $notifications,
                'unreadNotif'  => $unreadNotif,
            ]));
        }

        if ($user->hasApprovedRole(Role::DRIVER)) {
            $data = $this->stats->getDriverDashboard($user->getId());
            return $this->render('dashboard.driver', array_merge($data, [
                'title'        => 'Tableau de bord Conducteur',
                'notifications'=> $notifications,
                'unreadNotif'  => $unreadNotif,
            ]));
        }

        $data = $this->stats->getPassengerDashboard($user->getId());
        return $this->render('dashboard.passenger', array_merge($data, [
            'title'        => 'Tableau de bord',
            'notifications'=> $notifications,
            'unreadNotif'  => $unreadNotif,
        ]));
    }
}
