<?php
declare(strict_types=1);
namespace App\Middlewares;

use Core\{Request, Response, Session};
use App\Enums\Role;
use App\Interfaces\UserRepositoryInterface;
use App\Exceptions\AuthorizationException;

class RoleMiddleware
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepo,
        private readonly Role $requiredRole
    ) {}

    public function handle(Request $request): ?Response
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return Response::redirect('/login');
        }

        $user = $this->userRepo->findWithRoles((int)$userId);
        if (!$user || !$user->hasApprovedRole($this->requiredRole)) {
            if ($request->wantsJson()) {
                return Response::json(['error' => 'Accès refusé'], 403);
            }
            Session::flash('error', 'Accès refusé : rôle insuffisant.');
            return Response::redirect('/dashboard');
        }
        return null;
    }
}
