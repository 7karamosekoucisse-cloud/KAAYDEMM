<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{View, Response, Session, Request};
use App\Helpers\Csrf;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface};

abstract class AbstractController
{
    public function __construct(
        protected readonly AuthServiceInterface    $auth,
        protected readonly UserRepositoryInterface $userRepo
    ) {}

    protected function render(string $view, array $data = [], string $layout = 'layouts.main'): Response
    {
        $user = $this->auth->currentUser();
        $data = array_merge([
            'currentUser'   => $user,
            'flashSuccess'  => Session::getFlash('success'),
            'flashError'    => Session::getFlash('error'),
            'flashInfo'     => Session::getFlash('info'),
            'csrfToken'     => Csrf::token(),
        ], $data);
        return Response::html(View::make($view, $data, $layout));
    }

    protected function redirect(string $path, string $flash = '', string $flashType = 'success'): Response
    {
        if ($flash) Session::flash($flashType, $flash);
        return Response::redirect($path);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function requireAuth(): void
    {
        if (!$this->auth->isAuthenticated()) {
            throw new \App\Exceptions\AuthorizationException('Non authentifié');
        }
    }

    protected function requireRole(\App\Enums\Role $role): void
    {
        $user = $this->auth->currentUser();
        if (!$user || !$user->hasApprovedRole($role)) {
            throw new \App\Exceptions\AuthorizationException('Rôle insuffisant');
        }
    }
}
