<?php
declare(strict_types=1);
namespace App\Services;

use Core\Logger;
use App\Services\EmailService;
use App\Models\{User, UserRole, DriverProfile};
use App\Enums\{Role, RoleStatus};
use App\Interfaces\{UserRepositoryInterface, NotifierInterface};
use App\Exceptions\{AuthorizationException, NotFoundException, ValidationException};
use App\Helpers\Validator;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepo,
        private readonly NotifierInterface       $notifier
    ) {}

    public function updateProfile(User $user, array $data, ?array $avatarFile = null): User
    {
        if (!empty($data['first_name'])) $user->setFirstName($data['first_name']);
        if (!empty($data['last_name']))  $user->setLastName($data['last_name']);
        if (!empty($data['phone']))      $user->setPhone($data['phone']);
        if (!empty($data['campus']))     $user->setCampus($data['campus']);

        if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
            $avatar = $this->handleAvatarUpload($avatarFile, $user->getId());
            if ($avatar) $user->setAvatar($avatar);
        }

        $this->userRepo->save($user);
        return $user;
    }

    private function handleAvatarUpload(array $file, int $userId): ?string
    {
        $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        $maxSize = $cfg['upload']['max_size'];
        $allowed = $cfg['upload']['allowed'];

        if ($file['size'] > $maxSize) return null;
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowed)) return null;

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = "avatar_{$userId}_" . time() . ".{$ext}";
        $dest = $cfg['upload']['path'] . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $name;
        }
        return null;
    }

    public function requestDriverRole(User $user, array $data, ?array $licenseFile = null): UserRole
    {
        // Bloquer uniquement si la demande est en attente ou déjà approuvée
        // Un conducteur REJETÉ peut re-soumettre un dossier
        $existing = $user->getUserRole(Role::DRIVER);
        if ($existing && $existing->getStatus() !== RoleStatus::REJECTED) {
            throw new ValidationException(['role' => 'Vous avez déjà fait une demande de rôle conducteur.']);
        }

        Validator::make()
            ->required($data, ['license_number', 'vehicle_brand', 'vehicle_model', 'vehicle_color', 'plate_number'])
            ->throwIfErrors();

        $dp = new DriverProfile();
        $dp->setUserId($user->getId());
        $dp->setLicenseNumber($data['license_number']);
        $dp->setVehicleBrand($data['vehicle_brand']);
        $dp->setVehicleModel($data['vehicle_model']);
        $dp->setVehicleColor($data['vehicle_color']);
        $dp->setPlateNumber($data['plate_number']);
        $dp->setDefaultSeats((int)($data['default_seats'] ?? 3));
        $dp->validate();

        if ($licenseFile && $licenseFile['error'] === UPLOAD_ERR_OK) {
            $cfg  = require dirname(__DIR__, 2) . '/config/config.php';
            $name = 'license_' . $user->getId() . '_' . time() . '.jpg';
            move_uploaded_file($licenseFile['tmp_name'], $cfg['upload']['path'] . $name);
            $dp->setLicenseDoc($name);
        }

        $this->userRepo->saveDriverProfile($dp);

        $ur = new UserRole();
        $ur->setUserId($user->getId());
        $ur->setRole(Role::DRIVER);
        $ur->setStatus(RoleStatus::PENDING);
        $this->userRepo->saveUserRole($ur);

        Logger::info('Driver role requested', ['user_id' => $user->getId()]);
        return $ur;
    }

    public function approveDriver(User $admin, int $userId): void
    {
        if (!$admin->hasApprovedRole(Role::ADMIN)) throw new AuthorizationException();

        $user = $this->userRepo->findWithRoles($userId);
        if (!$user) throw new NotFoundException('Utilisateur introuvable');

        $ur = $user->getUserRole(Role::DRIVER);
        if (!$ur) throw new NotFoundException('Demande de rôle conducteur introuvable');

        $ur->approve($admin->getId());
        $this->userRepo->saveUserRole($ur);

        Logger::info('Driver approved', ['user_id' => $userId, 'admin' => $admin->getId()]);
    }

    public function rejectDriver(User $admin, int $userId, string $reason): void
    {
        if (!$admin->hasApprovedRole(Role::ADMIN)) throw new AuthorizationException();
        $user = $this->userRepo->findWithRoles($userId);
        if (!$user) throw new NotFoundException();
        $ur = $user->getUserRole(Role::DRIVER);
        if (!$ur) throw new NotFoundException();
        $ur->reject($admin->getId(), $reason);
        $this->userRepo->saveUserRole($ur);
    }

    public function banUser(User $admin, int $userId): void
    {
        if (!$admin->hasApprovedRole(Role::ADMIN)) throw new AuthorizationException();
        $user = $this->userRepo->find($userId);
        if (!$user) throw new NotFoundException();
        $user->setIsBanned(true);
        $user->setIsActive(false);
        $this->userRepo->save($user);
    }

    public function unbanUser(User $admin, int $userId): void
    {
        if (!$admin->hasApprovedRole(Role::ADMIN)) throw new AuthorizationException();
        $user = $this->userRepo->find($userId);
        if (!$user) throw new NotFoundException();
        $user->setIsBanned(false);
        $user->setIsActive(true);
        $this->userRepo->save($user);
    }

    public function updateTrustScore(int $userId): void
    {
        $stats = $this->userRepo->getUserStats($userId);
        $score = 50.0;
        if (!empty($stats['avg_rating'])) {
            $score += ($stats['avg_rating'] - 3) * 15;
        }
        if (!empty($stats['review_count'])) {
            $score += min(20, $stats['review_count'] * 2);
        }
        $score = max(0, min(100, $score));

        $user = $this->userRepo->find($userId);
        if ($user) {
            $user->setTrustScore($score);
            $this->userRepo->save($user);
        }
    }
}
