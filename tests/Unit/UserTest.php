<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\{User, UserRole};
use App\Enums\{Role, RoleStatus};

class UserTest extends TestCase
{
    private function makeUser(): User
    {
        $user = new User();
        $user->setId(1);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test@ucad.sn');
        $user->setPhone('771234567');
        $user->setCampus('UCAD');
        return $user;
    }

    private function makeRole(Role $role, RoleStatus $status): UserRole
    {
        $ur = new UserRole();
        $ur->setRole($role);
        $ur->setStatus($status);
        return $ur;
    }

    public function testHasRoleReturnsTrueWhenRoleExists(): void
    {
        $user = $this->makeUser();
        $user->setUserRoles([$this->makeRole(Role::PASSENGER, RoleStatus::APPROVED)]);
        $this->assertTrue($user->hasRole(Role::PASSENGER));
    }

    public function testHasRoleReturnsFalseWhenMissing(): void
    {
        $user = $this->makeUser();
        $user->setUserRoles([]);
        $this->assertFalse($user->hasRole(Role::DRIVER));
    }

    public function testHasApprovedRoleRejectsNonApproved(): void
    {
        $user = $this->makeUser();
        $user->setUserRoles([$this->makeRole(Role::DRIVER, RoleStatus::PENDING)]);
        $this->assertFalse($user->hasApprovedRole(Role::DRIVER));
    }

    public function testHasApprovedRoleAcceptsApproved(): void
    {
        $user = $this->makeUser();
        $user->setUserRoles([$this->makeRole(Role::DRIVER, RoleStatus::APPROVED)]);
        $this->assertTrue($user->hasApprovedRole(Role::DRIVER));
    }

    public function testUserCanHaveMultipleRoles(): void
    {
        $user = $this->makeUser();
        $user->setUserRoles([
            $this->makeRole(Role::PASSENGER, RoleStatus::APPROVED),
            $this->makeRole(Role::DRIVER,    RoleStatus::APPROVED),
        ]);
        $this->assertTrue($user->hasApprovedRole(Role::PASSENGER));
        $this->assertTrue($user->hasApprovedRole(Role::DRIVER));
        $this->assertFalse($user->hasApprovedRole(Role::ADMIN));
    }

    public function testIsLockedWhenLockedUntilInFuture(): void
    {
        $user = $this->makeUser();
        $user->setLockedUntil(new \DateTimeImmutable('+1 hour'));
        $this->assertTrue($user->isLocked());
    }

    public function testNotLockedWhenLockedUntilNull(): void
    {
        $user = $this->makeUser();
        $user->setLockedUntil(null);
        $this->assertFalse($user->isLocked());
    }

    public function testTrustScoreDefault(): void
    {
        $user = $this->makeUser();
        $this->assertEquals(50.0, $user->getTrustScore());
    }
}
