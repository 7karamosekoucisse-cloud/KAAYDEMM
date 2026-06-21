<?php
declare(strict_types=1);
namespace App\Middlewares;

use Core\{Request, Response, Session};
use App\Exceptions\AuthorizationException;

class AuthMiddleware
{
    public function handle(Request $request): ?Response
    {
        if (!Session::has('user_id')) {
            Session::flash('error', 'Veuillez vous connecter pour accéder à cette page.');
            if ($request->wantsJson()) {
                return Response::json(['error' => 'Non authentifié'], 401);
            }
            return Response::redirect('/login?redirect=' . urlencode($request->path()));
        }
        return null;
    }
}
