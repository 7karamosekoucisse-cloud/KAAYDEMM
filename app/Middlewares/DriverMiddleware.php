<?php
declare(strict_types=1);
namespace App\Middlewares;

use Core\{Request, Response, Session};
use App\Enums\Role;
use App\Interfaces\UserRepositoryInterface;

class DriverMiddleware
{
    public function __construct(private readonly UserRepositoryInterface $userRepo) {}

    public function handle(Request $request): ?Response
    {
        $userId = Session::get('user_id');
        if (!$userId) return Response::redirect('/login');
        $user = $this->userRepo->findWithRoles((int)$userId);
        if (!$user || !$user->hasApprovedRole(Role::DRIVER)) {
            Session::flash('error', 'Votre compte conducteur n\'est pas activé.');
            return Response::redirect('/dashboard');
        }
        return null;
    }
}
