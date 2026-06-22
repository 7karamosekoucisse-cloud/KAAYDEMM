<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\Validator;
use App\Exceptions\ValidationException;

class ValidatorTest extends TestCase
{
    public function testRequiredFieldsPass(): void
    {
        $v = Validator::make()->required(['name' => 'Mamadou'], ['name']);
        $this->assertFalse($v->hasErrors());
    }

    public function testRequiredFieldsFail(): void
    {
        $v = Validator::make()->required(['name' => ''], ['name']);
        $this->assertTrue($v->hasErrors());
        $this->assertArrayHasKey('name', $v->getErrors());
    }

    public function testEmailValidation(): void
    {
        $v1 = Validator::make()->email(['email' => 'test@ucad.sn'], 'email');
        $this->assertFalse($v1->hasErrors());

        $v2 = Validator::make()->email(['email' => 'not-an-email'], 'email');
        $this->assertTrue($v2->hasErrors());
    }

    public function testPasswordStrengthWeak(): void
    {
        $v = Validator::make()->passwordStrength(['password' => 'simple'], 'password');
        $this->assertTrue($v->hasErrors());
    }

    public function testPasswordStrengthStrong(): void
    {
        $v = Validator::make()->passwordStrength(['password' => 'Secure123'], 'password');
        $this->assertFalse($v->hasErrors());
    }

    public function testPasswordConfirmed(): void
    {
        $v1 = Validator::make()->confirmed(['password' => 'Abc123', 'confirm' => 'Abc123'], 'password', 'confirm');
        $this->assertFalse($v1->hasErrors());

        $v2 = Validator::make()->confirmed(['password' => 'Abc123', 'confirm' => 'Different'], 'password', 'confirm');
        $this->assertTrue($v2->hasErrors());
    }

    public function testSenegalPhoneValidation(): void
    {
        $valid = ['770000000', '781234567', '703456789', '763456789'];
        foreach ($valid as $phone) {
            $v = Validator::make()->phone(['phone' => $phone], 'phone');
            $this->assertFalse($v->hasErrors(), "Phone $phone should be valid");
        }

        $invalid = ['0612345678', '1234', 'abcdefg'];
        foreach ($invalid as $phone) {
            $v = Validator::make()->phone(['phone' => $phone], 'phone');
            $this->assertTrue($v->hasErrors(), "Phone $phone should be invalid");
        }
    }

    public function testThrowIfErrors(): void
    {
        $this->expectException(ValidationException::class);
        Validator::make()->required(['email' => ''], ['email'])->throwIfErrors();
    }

    public function testBetweenValidation(): void
    {
        $v1 = Validator::make()->between(['seats' => '3'], 'seats', 1, 8);
        $this->assertFalse($v1->hasErrors());

        $v2 = Validator::make()->between(['seats' => '9'], 'seats', 1, 8);
        $this->assertTrue($v2->hasErrors());
    }
}
