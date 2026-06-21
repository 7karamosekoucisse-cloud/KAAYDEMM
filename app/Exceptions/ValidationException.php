<?php declare(strict_types=1);
namespace App\Exceptions;
class ValidationException extends AppException
{
    public function __construct(
        private readonly array $errors,
        string $msg = 'Données invalides'
    ) { parent::__construct($msg, $msg, 422); }

    public function getErrors(): array { return $this->errors; }
    public function first(): string { return array_values($this->errors)[0] ?? $this->getMessage(); }
}
