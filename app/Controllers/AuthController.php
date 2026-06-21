<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\{Request, Response, Session};
use App\Services\AuthService;
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface};
use App\Exceptions\{AuthenticationException, ValidationException};
use App\Helpers\Csrf;

class AuthController extends AbstractController
{
    public function __construct(
        AuthServiceInterface $auth,
        UserRepositoryInterface $userRepo,
        private readonly AuthService $authService
    ) {
        parent::__construct($auth, $userRepo);
    }

    public function showRegister(Request $req): Response
    {
        if ($this->auth->isAuthenticated()) return $this->redirect('/dashboard');
        return $this->render('auth.register', ['title' => 'Inscription'], 'layouts.auth');
    }

    public function register(Request $req): Response
    {
        try {
            Csrf::verify($req->all());
            $user = $this->authService->register($req->all());
            Session::flash('success', 'Compte créé avec succès ! Bienvenue sur Kaay Dem !');
            return $this->redirect('/login');
        } catch (ValidationException $e) {
            Session::set('_old.email', $req->post('email'));
            Session::set('_old.first_name', $req->post('first_name'));
            Session::set('_old.last_name', $req->post('last_name'));
            Session::set('_old.campus', $req->post('campus'));
            return $this->render('auth.register', [
                'title'  => 'Inscription',
                'errors' => $e->getErrors(),
            ], 'layouts.auth');
        }
    }

    public function showLogin(Request $req): Response
    {
        if ($this->auth->isAuthenticated()) return $this->redirect('/dashboard');
        return $this->render('auth.login', ['title' => 'Connexion'], 'layouts.auth');
    }

    public function login(Request $req): Response
    {
        try {
            Csrf::verify($req->all());
            $user = $this->authService->login(
                $req->post('email', ''),
                $req->post('password', ''),
                $req->ip()
            );
            if ($req->post('remember')) {
                $token = $this->authService->setRememberToken($user);
                setcookie('remember', $token, [
                    'expires'  => time() + 30 * 86400,
                    'path'     => '/',
                    'secure'   => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            $redirect = $req->get('redirect', '/dashboard');
            return $this->redirect($redirect, 'Bienvenue, ' . $user->getFirstName() . ' !');
        } catch (AuthenticationException $e) {
            return $this->render('auth.login', [
                'title' => 'Connexion',
                'error' => $e->getMessage(),
                'email' => $req->post('email'),
            ], 'layouts.auth');
        }
    }

    public function logout(Request $req): Response
    {
        $this->authService->logout();
        setcookie('remember', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        return $this->redirect('/login', 'Vous êtes déconnecté.');
    }

    public function showForgotPassword(Request $req): Response
    {
        return $this->render('auth.forgot', ['title' => 'Mot de passe oublié'], 'layouts.auth');
    }

    public function forgotPassword(Request $req): Response
    {
        Csrf::verify($req->all());
        $this->authService->sendPasswordReset($req->post('email', ''));
        return $this->render('auth.forgot', [
            'title'   => 'Mot de passe oublié',
            'success' => 'Si cet email existe, un lien de réinitialisation a été envoyé.',
        ], 'layouts.auth');
    }

    public function showResetPassword(Request $req, string $token): Response
    {
        return $this->render('auth.reset', ['title' => 'Nouveau mot de passe', 'token' => $token], 'layouts.auth');
    }

    public function resetPassword(Request $req, string $token): Response
    {
        try {
            Csrf::verify($req->all());
            $this->authService->resetPassword($token, $req->post('password', ''), $req->post('password_confirm', ''));
            return $this->redirect('/login', 'Mot de passe mis à jour. Connectez-vous.');
        } catch (ValidationException $e) {
            return $this->render('auth.reset', [
                'title'  => 'Nouveau mot de passe',
                'token'  => $token,
                'errors' => $e->getErrors(),
            ], 'layouts.auth');
        }
    }
}
