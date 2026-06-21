<?php declare(strict_types=1);
namespace App\Exceptions;
class InvalidTransitionException extends BookingException
{
    public function __construct(string $msg = 'Transition de statut non autorisée')
    { parent::__construct($msg); }
}
