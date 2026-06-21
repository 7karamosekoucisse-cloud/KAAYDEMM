<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\{User, UserRole, DriverProfile, AbstractEntity};
use App\Interfaces\UserRepositoryInterface;
use App\Enums\{Role, RoleStatus};

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    protected string $table      = 'users';
    protected string $modelClass = User::class;

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var User $entity */
        if ($entity->isNew()) {
            $sql = 'INSERT INTO users (email, password_hash, first_name, last_name, phone, campus, avatar, is_active, trust_score, loyalty_points, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())';
            $this->execute($sql, [
                $entity->getEmail(), $entity->getPasswordHash(),
                $entity->getFirstName(), $entity->getLastName(),
                $entity->getPhone(), $entity->getCampus(),
                $entity->getAvatar(), (int)$entity->isActive(),
                $entity->getTrustScore(), $entity->getLoyaltyPoints(),
            ]);
            $entity->setId((int)$this->pdo->lastInsertId());
        } else {
            $sql = 'UPDATE users SET email=?, password_hash=?, first_name=?, last_name=?, phone=?, campus=?, avatar=?, is_active=?, is_banned=?, trust_score=?, loyalty_points=?, login_attempts=?, locked_until=?, reset_token=?, reset_token_expiry=?, remember_token=?, total_trips=?, total_bookings=?, updated_at=NOW() WHERE id=?';
            $this->execute($sql, [
                $entity->getEmail(), $entity->getPasswordHash(),
                $entity->getFirstName(), $entity->getLastName(),
                $entity->getPhone(), $entity->getCampus(),
                $entity->getAvatar(), (int)$entity->isActive(),
                (int)$entity->isBanned(), $entity->getTrustScore(),
                $entity->getLoyaltyPoints(), $entity->getLoginAttempts(),
                $entity->getLockedUntil()?->format('Y-m-d H:i:s'),
                $entity->getResetToken(),
                $entity->getResetTokenExpiry()?->format('Y-m-d H:i:s'),
                $entity->getRememberToken(),
                $entity->getTotalTrips(), $entity->getTotalBookings(),
                $entity->getId(),
            ]);
        }
        return $entity;
    }

    protected function hydrate(array $row): User
    {
        $user = new User();
        $user->setId((int)$row['id']);
        $user->setEmail($row['email']);
        $user->setPasswordHash($row['password_hash']);
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setPhone($row['phone']);
        $user->setCampus($row['campus']);
        $user->setAvatar($row['avatar'] ?? null);
        $user->setIsActive((bool)($row['is_active'] ?? true));
        $user->setIsBanned((bool)($row['is_banned'] ?? false));
        $user->setTrustScore((float)($row['trust_score'] ?? 50));
        $user->setLoyaltyPoints((int)($row['loyalty_points'] ?? 0));
        $user->setLoginAttempts((int)($row['login_attempts'] ?? 0));
        $user->setTotalTrips((int)($row['total_trips'] ?? 0));
        $user->setTotalBookings((int)($row['total_bookings'] ?? 0));
        $user->setRememberToken($row['remember_token'] ?? null);
        $user->setResetToken($row['reset_token'] ?? null);
        if (!empty($row['reset_token_expiry'])) {
            $user->setResetTokenExpiry(new \DateTimeImmutable($row['reset_token_expiry']));
        }
        if (!empty($row['locked_until'])) {
            $user->setLockedUntil(new \DateTimeImmutable($row['locked_until']));
        }
        $user->setCreatedAt(!empty($row['created_at']) ? new \DateTimeImmutable($row['created_at']) : null);
        $user->setUpdatedAt(!empty($row['updated_at']) ? new \DateTimeImmutable($row['updated_at']) : null);
        return $user;
    }

    public function findWithRoles(int $id): ?User
    {
        $user = $this->find($id);
        if (!$user) return null;
        $this->loadRoles($user);
        $this->loadDriverProfile($user);
        return $user;
    }

    private function loadRoles(User $user): void
    {
        $rows = $this->fetchAll('SELECT * FROM user_roles WHERE user_id = ?', [$user->getId()]);
        $roles = [];
        foreach ($rows as $row) {
            $ur = new UserRole();
            $ur->setId((int)$row['id']);
            $ur->setUserId((int)$row['user_id']);
            $ur->setRole(Role::from($row['role_code']));
            $ur->setStatus(RoleStatus::from($row['status']));
            $ur->setGrantedAt(!empty($row['granted_at']) ? new \DateTimeImmutable($row['granted_at']) : null);
            $ur->setValidatedBy($row['validated_by'] ? (int)$row['validated_by'] : null);
            $ur->setValidatedAt(!empty($row['validated_at']) ? new \DateTimeImmutable($row['validated_at']) : null);
            $ur->setRejectionReason($row['rejection_reason'] ?? null);
            $roles[] = $ur;
        }
        $user->setUserRoles($roles);
    }

    private function loadDriverProfile(User $user): void
    {
        $row = $this->fetchOne('SELECT * FROM driver_profiles WHERE user_id = ?', [$user->getId()]);
        if ($row) {
            $dp = new DriverProfile();
            $dp->setId((int)$row['id']);
            $dp->setUserId((int)$row['user_id']);
            $dp->setLicenseNumber($row['license_number']);
            $dp->setVehicleBrand($row['vehicle_brand']);
            $dp->setVehicleModel($row['vehicle_model']);
            $dp->setVehicleColor($row['vehicle_color']);
            $dp->setPlateNumber($row['plate_number']);
            $dp->setDefaultSeats((int)$row['default_seats']);
            $dp->setLicenseDoc($row['license_doc'] ?? null);
            $user->setDriverProfile($dp);
        }
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne('SELECT * FROM users WHERE email = ?', [strtolower(trim($email))]);
        if (!$row) return null;
        $user = $this->hydrate($row);
        $this->loadRoles($user);
        return $user;
    }

    public function findByRememberToken(string $token): ?User
    {
        $row = $this->fetchOne('SELECT * FROM users WHERE remember_token = ?', [$token]);
        if (!$row) return null;
        $user = $this->hydrate($row);
        $this->loadRoles($user);
        return $user;
    }

    public function findByResetToken(string $token): ?User
    {
        $row = $this->fetchOne(
            'SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()',
            [$token]
        );
        if (!$row) return null;
        return $this->hydrate($row);
    }

    public function findPendingDrivers(): array
    {
        $rows = $this->fetchAll(
            "SELECT u.*, ur.granted_at AS requested_at FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             WHERE ur.role_code = 'DRIVER' AND ur.status = 'PENDING'
             ORDER BY ur.granted_at ASC"
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function search(string $query, int $limit = 20): array
    {
        $q = "%$query%";
        $rows = $this->fetchAll(
            'SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? LIMIT ?',
            [$q, $q, $q, $limit]
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }

    public function saveUserRole(UserRole $ur): UserRole
    {
        if ($ur->isNew()) {
            $this->execute(
                'INSERT INTO user_roles (user_id, role_code, status, granted_at) VALUES (?,?,?,NOW())',
                [$ur->getUserId(), $ur->getRole()->value, $ur->getStatus()->value]
            );
            $ur->setId((int)$this->pdo->lastInsertId());
        } else {
            $this->execute(
                'UPDATE user_roles SET status=?, validated_by=?, validated_at=?, rejection_reason=? WHERE id=?',
                [
                    $ur->getStatus()->value, $ur->getValidatedBy(),
                    $ur->getValidatedAt()?->format('Y-m-d H:i:s'),
                    $ur->getRejectionReason(), $ur->getId(),
                ]
            );
        }
        return $ur;
    }

    public function saveDriverProfile(DriverProfile $dp): DriverProfile
    {
        $existing = $this->fetchOne('SELECT id FROM driver_profiles WHERE user_id = ?', [$dp->getUserId()]);
        if ($existing) {
            $this->execute(
                'UPDATE driver_profiles SET license_number=?, vehicle_brand=?, vehicle_model=?, vehicle_color=?, plate_number=?, default_seats=?, license_doc=? WHERE user_id=?',
                [$dp->getLicenseNumber(), $dp->getVehicleBrand(), $dp->getVehicleModel(), $dp->getVehicleColor(), $dp->getPlateNumber(), $dp->getDefaultSeats(), $dp->getLicenseDoc(), $dp->getUserId()]
            );
            $dp->setId((int)$existing['id']);
        } else {
            $this->execute(
                'INSERT INTO driver_profiles (user_id, license_number, vehicle_brand, vehicle_model, vehicle_color, plate_number, default_seats, license_doc) VALUES (?,?,?,?,?,?,?,?)',
                [$dp->getUserId(), $dp->getLicenseNumber(), $dp->getVehicleBrand(), $dp->getVehicleModel(), $dp->getVehicleColor(), $dp->getPlateNumber(), $dp->getDefaultSeats(), $dp->getLicenseDoc()]
            );
            $dp->setId((int)$this->pdo->lastInsertId());
        }
        return $dp;
    }

    public function getUserStats(int $userId): array
    {
        $row = $this->fetchOne(
            "SELECT
               (SELECT COUNT(*) FROM trips WHERE driver_id = ? AND status='COMPLETED') as trips_as_driver,
               (SELECT COUNT(*) FROM bookings WHERE passenger_id = ? AND status='COMPLETED') as trips_as_passenger,
               (SELECT AVG(rating) FROM reviews WHERE target_id = ?) as avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE target_id = ?) as review_count",
            [$userId, $userId, $userId, $userId]
        );
        return $row ?? [];
    }

    public function getLeaderboard(int $limit = 10): array
    {
        // Inclure TOUS les conducteurs approuvés, même sans trajet complété
        $rows = $this->fetchAll(
            "SELECT u.id, u.first_name, u.last_name, u.avatar, u.trust_score,
                    COUNT(DISTINCT t.id) as trip_count,
                    ROUND(AVG(r.rating), 1) as avg_rating,
                    COUNT(r.id) as review_count
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id AND ur.role_code = 'DRIVER' AND ur.status = 'APPROVED'
             LEFT JOIN trips t ON t.driver_id = u.id
             LEFT JOIN reviews r ON r.target_id = u.id
             GROUP BY u.id, u.first_name, u.last_name, u.avatar, u.trust_score
             ORDER BY avg_rating DESC, trip_count DESC
             LIMIT ?",
            [$limit]
        );
        // Fallback : si vraiment vide, retourner tous les conducteurs approuvés
        if (empty($rows)) {
            $rows = $this->fetchAll(
                "SELECT u.id, u.first_name, u.last_name, u.avatar, u.trust_score,
                        0 as trip_count, 0 as avg_rating, 0 as review_count
                 FROM users u
                 JOIN user_roles ur ON ur.user_id = u.id AND ur.role_code = 'DRIVER' AND ur.status = 'APPROVED'
                 LIMIT ?",
                [$limit]
            );
        }
        return $rows;
    }

    public function countAll(): int
    {
        return (int)($this->fetchOne('SELECT COUNT(*) as c FROM users')['c'] ?? 0);
    }

    public function findAllPaginated(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $rows = $this->fetchAll(
            "SELECT u.*, (SELECT status FROM user_roles WHERE user_id=u.id AND role_code='DRIVER') as driver_status
             FROM users u ORDER BY u.created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        return array_map(fn($r) => $this->hydrate($r), $rows);
    }
}
