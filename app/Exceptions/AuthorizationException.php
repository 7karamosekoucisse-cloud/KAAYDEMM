<?php declare(strict_types=1);
namespace App\Exceptions;
class AuthorizationException extends AppException
{
    public function __construct(string $msg = 'Accès refusé')
    { parent::__construct($msg, $msg, 403); }
}
