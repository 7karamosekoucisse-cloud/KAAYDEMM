<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response, Session};
use App\Services\{UserService, ReviewService};
use App\Repositories\ReviewRepository;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface};
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;

class ProfileController extends AbstractController
{
    public function __construct(
        AuthServiceInterface    $auth,
        UserRepositoryInterface $userRepo,
        private readonly UserService    $userService,
        private readonly ReviewRepository $reviewRepo
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function show(Request $req, string $id): Response
    {
        $user = $this->userRepo->findWithRoles((int)$id);
        if (!$user) return $this->render('errors.404', ['title' => 'Utilisateur introuvable']);
        $reviews = $this->reviewRepo->findByTarget((int)$id);
        $stats   = $this->reviewRepo->getDetailedStats((int)$id);
        $globalStats = $this->userRepo->getUserStats((int)$id);
        return $this->render('profile.show', [
            'title'       => 'Profil de ' . $user->getFullName(),
            'profile'     => $user,
            'reviews'     => $reviews,
            'stats'       => $stats,
            'globalStats' => $globalStats,
        ]);
    }

    public function edit(Request $req): Response
    {
        $this->requireAuth();
        $user = $this->auth->currentUser();
        return $this->render('profile.edit', [
            'title' => 'Mon profil',
            'user'  => $user,
        ]);
    }

    public function update(Request $req): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user = $this->auth->currentUser();
            $this->userService->updateProfile($user, $req->all(), $req->file('avatar'));
            return $this->redirect('/profile/edit', 'Profil mis à jour avec succès.');
        } catch (ValidationException $e) {
            Session::flash('errors', $e->getErrors());
            return $this->redirect('/profile/edit');
        }
    }

    public function requestDriver(Request $req): Response
    {
        $this->requireAuth();
        return $this->render('profile.driver_request', [
            'title' => 'Devenir conducteur',
        ]);
    }

    public function submitDriverRequest(Request $req): Response
    {
        $this->requireAuth();
        try {
            Csrf::verify($req->all());
            $user = $this->auth->currentUser();
            $this->userService->requestDriverRole($user, $req->all(), $req->file('license_doc'));
            return $this->redirect('/dashboard', 'Votre demande a été soumise. Elle sera examinée sous 24h.');
        } catch (ValidationException $e) {
            return $this->render('profile.driver_request', [
                'title'  => 'Devenir conducteur',
                'errors' => $e->getErrors(),
                'old'    => $req->all(),
            ]);
        }
    }
}
