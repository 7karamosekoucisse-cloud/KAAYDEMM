<?php declare(strict_types=1);
namespace App\Exceptions;
class NotFoundException extends AppException
{
    public function __construct(string $msg = 'Ressource introuvable')
    { parent::__construct($msg, $msg, 404); }
}
