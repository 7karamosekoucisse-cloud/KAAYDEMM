<?php
declare(strict_types=1);
namespace App\Traits;

use Core\Logger;

trait Loggable
{
    protected function log(string $level, string $message, array $context = []): void
    {
        Logger::log($level, '[' . static::class . '] ' . $message, $context);
    }
}
