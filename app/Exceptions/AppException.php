<?php
declare(strict_types=1);
namespace App\Exceptions;

class AppException extends \RuntimeException
{
    public function __construct(
        string $message = '',
        private readonly string $userMessage = '',
        private readonly int $httpCode = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $httpCode, $previous);
    }

    public function getUserMessage(): string { return $this->userMessage ?: $this->getMessage(); }
    public function getHttpCode(): int { return $this->httpCode; }
}
