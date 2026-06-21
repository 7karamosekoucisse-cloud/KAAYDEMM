<?php
declare(strict_types=1);
namespace App\Middlewares;

use Core\{Request, Response, Session};
use App\Helpers\Csrf;
use App\Exceptions\CsrfException;

class CsrfMiddleware
{
    public function handle(Request $request): ?Response
    {
        if ($request->isPost()) {
            try {
                Csrf::check($request->post('_csrf'));
            } catch (CsrfException $e) {
                if ($request->wantsJson()) {
                    return Response::json(['error' => 'Jeton CSRF invalide'], 403);
                }
                Session::flash('error', 'Requête invalide. Veuillez réessayer.');
                return Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        }
        return null;
    }
}
