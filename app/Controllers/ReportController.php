<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response};
use App\Models\Report;
use App\Repositories\ReportRepository;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface};
use App\Helpers\Csrf;

class ReportController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly ReportRepository $reportRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function store(Request $req): Response
    {
        $this->requireAuth();
        Csrf::verify($req->all());
        $user   = $this->auth->currentUser();
        $report = new Report();
        $report->setReporterId($user->getId());
        $report->setTargetId((int)$req->post('target_id'));
        $report->setTargetType($req->post('target_type', 'user'));
        $report->setReason($req->post('reason', ''));
        $report->setDetails($req->post('details'));
        $report->setStatus('PENDING');
        $this->reportRepo->save($report);

        if ($req->wantsJson()) return $this->json(['success' => true]);
        return $this->redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard', 'Signalement envoyé, merci.');
    }
}
