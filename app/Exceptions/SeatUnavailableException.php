<?php declare(strict_types=1);
namespace App\Exceptions;
class SeatUnavailableException extends BookingException
{
    public function __construct(string $msg = 'Plus assez de places disponibles')
    { parent::__construct($msg); }
}
