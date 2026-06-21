<?php declare(strict_types=1);
namespace App\Exceptions;
class AuthenticationException extends AppException
{
    public function __construct(string $msg = 'Identifiants invalides')
    { parent::__construct($msg, $msg, 401); }
}
