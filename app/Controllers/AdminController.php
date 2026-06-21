<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response, Session};
use App\Services\{UserService, StatisticsService};
use App\Repositories\{ReportRepository};
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface, TripRepositoryInterface};
use App\Enums\Role;
use App\Exceptions\{ValidationException, AuthorizationException};
use App\Helpers\Csrf;

class AdminController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly TripRepositoryInterface $tripRepo,
        private readonly UserService             $userService,
        private readonly StatisticsService       $stats,
        private readonly ReportRepository        $reportRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function index(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        $data = $this->stats->getAdminDashboard();
        return $this->render('admin.index', array_merge($data, ['title' => 'Administration']));
    }

    public function users(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        $page    = max(1, (int)$req->get('page', 1));
        $query   = $req->get('q', '');
        $users   = $query
            ? $this->userRepo->search($query)
            : $this->userRepo->findAllPaginated($page, 20);
        $total   = $this->userRepo->countAll();
        return $this->render('admin.users', [
            'title' => 'Gestion des utilisateurs',
            'users' => $users,
            'total' => $total,
            'page'  => $page,
            'pages' => (int)ceil($total / 20),
            'query' => $query,
        ]);
    }

    public function pendingDrivers(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        $pending = $this->userRepo->findPendingDrivers();
        return $this->render('admin.drivers', [
            'title'   => 'Demandes conducteur',
            'pending' => $pending,
        ]);
    }

    public function approveDriver(Request $req, string $userId): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        try {
            Csrf::verify($req->all());
            $admin = $this->auth->currentUser();
            $this->userService->approveDriver($admin, (int)$userId);
            return $this->redirect('/admin/drivers', 'Conducteur approuvé avec succès.');
        } catch (\Throwable $e) {
            return $this->redirect('/admin/drivers', $e->getMessage(), 'error');
        }
    }

    public function rejectDriver(Request $req, string $userId): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        try {
            Csrf::verify($req->all());
            $admin  = $this->auth->currentUser();
            $reason = $req->post('reason', 'Documents insuffisants');
            $this->userService->rejectDriver($admin, (int)$userId, $reason);
            return $this->redirect('/admin/drivers', 'Demande refusée.');
        } catch (\Throwable $e) {
            return $this->redirect('/admin/drivers', $e->getMessage(), 'error');
        }
    }

    public function banUser(Request $req, string $userId): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        try {
            Csrf::verify($req->all());
            $admin = $this->auth->currentUser();
            $this->userService->banUser($admin, (int)$userId);
            return $this->redirect('/admin/users', 'Utilisateur suspendu.');
        } catch (\Throwable $e) {
            return $this->redirect('/admin/users', $e->getMessage(), 'error');
        }
    }

    public function unbanUser(Request $req, string $userId): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        try {
            Csrf::verify($req->all());
            $admin = $this->auth->currentUser();
            $this->userService->unbanUser($admin, (int)$userId);
            return $this->redirect('/admin/users', 'Utilisateur réactivé.');
        } catch (\Throwable $e) {
            return $this->redirect('/admin/users', $e->getMessage(), 'error');
        }
    }

    public function trips(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        $page  = max(1, (int)$req->get('page', 1));
        $trips = $this->tripRepo->findAll(20, ($page - 1) * 20);
        $total = $this->tripRepo->countAll();
        return $this->render('admin.trips', [
            'title' => 'Gestion des trajets',
            'trips' => $trips,
            'total' => $total,
            'page'  => $page,
            'pages' => (int)ceil($total / 20),
        ]);
    }

    public function reports(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        $reports = $this->reportRepo->findPending();
        return $this->render('admin.reports', [
            'title'   => 'Signalements',
            'reports' => $reports,
        ]);
    }

    public function resolveReport(Request $req, string $id): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        Csrf::verify($req->all());
        $report = $this->reportRepo->find((int)$id);
        if ($report) {
            $report->setStatus('RESOLVED');
            $report->setResolvedBy($this->auth->currentUser()->getId());
            $report->setResolution($req->post('resolution', ''));
            $this->reportRepo->save($report);
        }
        return $this->redirect('/admin/reports', 'Signalement traité.');
    }

    public function stats(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        $data = $this->stats->getAdminDashboard();
        return $this->render('admin.stats', array_merge($data, ['title' => 'Statistiques']));
    }

    public function exportCsv(Request $req): Response
    {
        $this->requireAuth();
        $this->requireRole(Role::ADMIN);
        $csv = $this->stats->exportBookingsCsv();
        return new \Core\Response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bookings_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
