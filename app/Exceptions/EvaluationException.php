<?php declare(strict_types=1);
namespace App\Exceptions;
class EvaluationException extends AppException
{
    public function __construct(string $msg = 'Évaluation non autorisée')
    { parent::__construct($msg, $msg, 422); }
}
