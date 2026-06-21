<?php declare(strict_types=1);
namespace App\Exceptions;
class CsrfException extends AuthorizationException
{
    public function __construct() { parent::__construct('Jeton CSRF invalide'); }
}
