<?php declare(strict_types=1);
namespace App\Exceptions;
class BookingException extends AppException
{
    public function __construct(string $msg)
    { parent::__construct($msg, $msg, 422); }
}
