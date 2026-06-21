<?php
declare(strict_types=1);
namespace App\Services;

use Core\{Session, Logger};
use App\Services\EmailService;
use App\Models\{User, UserRole};
use App\Enums\{Role, RoleStatus};
use App\Interfaces\{AuthServiceInterface, UserRepositoryInterface};
use App\Exceptions\{AuthenticationException, ValidationException};
use App\Helpers\Validator;

class AuthService implements AuthServiceInterface
{
    private ?User $currentUser = null;

    private readonly EmailService $emailService;

    public function __construct(
        private readonly UserRepositoryInterface $userRepo
    ) {
        $this->emailService = new EmailService();
    }

    public function register(array $data): User
    {
        Validator::make()
            ->required($data, ['email', 'password', 'password_confirm', 'first_name', 'last_name', 'phone', 'campus'])
            ->email($data, 'email')
            ->passwordStrength($data, 'password')
            ->confirmed($data, 'password', 'password_confirm')
            ->minLength($data, 'first_name', 2)
            ->minLength($data, 'last_name', 2)
            ->phone($data, 'phone')
            ->throwIfErrors();

        if ($this->userRepo->findByEmail($data['email'])) {
            throw new ValidationException(['email' => 'Cette adresse email est déjà utilisée']);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPasswordHash(password_hash($data['password'], PASSWORD_DEFAULT));
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $user->setPhone($data['phone']);
        $user->setCampus($data['campus']);
        $user->validate();

        $this->userRepo->save($user);

        $ur = new UserRole();
        $ur->setUserId($user->getId());
        $ur->setRole(Role::PASSENGER);
        $ur->setStatus(RoleStatus::APPROVED);
        $this->userRepo->saveUserRole($ur);

        Logger::info('User registered', ['email' => $user->getEmail(), 'id' => $user->getId()]);
        // Email de bienvenue (asynchrone — ne bloque pas si échec)
        try { $this->emailService->sendWelcome($user->getEmail(), $user->getFirstName()); } catch (\Throwable) {}
        return $user;
    }

    public function login(string $email, string $password, string $ip = ''): User
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            Logger::security('Login failed - user not found', ['email' => $email, 'ip' => $ip]);
            throw new AuthenticationException('Identifiants invalides');
        }

        if ($user->isLocked()) {
            throw new AuthenticationException('Compte temporairement verrouillé. Réessayez plus tard.');
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            $attempts = $user->getLoginAttempts() + 1;
            $user->setLoginAttempts($attempts);
            $cfg = require dirname(__DIR__, 2) . '/config/config.php';
            if ($attempts >= $cfg['brute_force']['max_attempts']) {
                $lockout = new \DateTimeImmutable('+' . $cfg['brute_force']['lockout_time'] . ' seconds');
                $user->setLockedUntil($lockout);
                Logger::security('Account locked', ['email' => $email, 'ip' => $ip]);
            }
            $this->userRepo->save($user);
            throw new AuthenticationException('Identifiants invalides');
        }

        if (!$user->isActive() || $user->isBanned()) {
            throw new AuthenticationException('Votre compte a été suspendu. Contactez l\'administrateur.');
        }

        // Reset attempts, update session
        $user->setLoginAttempts(0);
        $user->setLockedUntil(null);
        $this->userRepo->save($user);

        Session::regenerate();
        Session::set('user_id', $user->getId());
        Session::set('user_name', $user->getFullName());

        Logger::info('User logged in', ['id' => $user->getId(), 'ip' => $ip]);
        $this->currentUser = $user;
        return $user;
    }

    public function loginWithRememberToken(string $token): ?User
    {
        $user = $this->userRepo->findByRememberToken($token);
        if (!$user || !$user->isActive()) return null;
        Session::regenerate();
        Session::set('user_id', $user->getId());
        $this->currentUser = $user;
        return $user;
    }

    public function setRememberToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $user->setRememberToken($token);
        $this->userRepo->save($user);
        return $token;
    }

    public function logout(): void
    {
        $userId = Session::get('user_id');
        Logger::info('User logged out', ['id' => $userId]);
        Session::destroy();
        $this->currentUser = null;
    }

    public function currentUser(): ?User
    {
        if ($this->currentUser) return $this->currentUser;
        $id = Session::get('user_id');
        if (!$id) return null;
        $this->currentUser = $this->userRepo->findWithRoles((int)$id);
        return $this->currentUser;
    }

    public function isAuthenticated(): bool
    {
        return Session::has('user_id');
    }

    public function sendPasswordReset(string $email): void
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) return; // silent — anti-enumeration

        $token = bin2hex(random_bytes(32));
        $user->setResetToken($token);
        $user->setResetTokenExpiry(new \DateTimeImmutable('+1 hour'));
        $this->userRepo->save($user);

        $resetLink = '';
        try {
            $cfg = require dirname(__DIR__, 2) . '/config/config.php';
            $resetLink = $cfg['app']['url'] . '/password/reset/' . $token;
        } catch (\Throwable) {}
        try { $this->emailService->sendPasswordReset($user->getEmail(), $user->getFirstName(), $resetLink); } catch (\Throwable) {}
        Logger::info('Password reset requested', ['email' => $email]);
    }

    public function resetPassword(string $token, string $password, string $confirm): void
    {
        if ($password !== $confirm) {
            throw new ValidationException(['password' => 'Les mots de passe ne correspondent pas']);
        }
        $user = $this->userRepo->findByResetToken($token);
        if (!$user) {
            throw new ValidationException(['token' => 'Lien invalide ou expiré']);
        }
        Validator::make()->passwordStrength(['password' => $password], 'password')->throwIfErrors();
        $user->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));
        $user->setResetToken(null);
        $user->setResetTokenExpiry(null);
        $this->userRepo->save($user);
    }
}
