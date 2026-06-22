<?php
declare(strict_types=1);
namespace Core;

final class Logger
{
    private static string $logFile = '';

    public static function setFile(string $path): void { self::$logFile = $path; }

    public static function log(string $level, string $message, array $context = []): void
    {
        $line = sprintf(
            "[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );
        if (self::$logFile) {
            file_put_contents(self::$logFile, $line, FILE_APPEND | LOCK_EX);
        }
    }

    public static function info(string $msg, array $ctx = []): void  { self::log('info',  $msg, $ctx); }
    public static function error(string $msg, array $ctx = []): void { self::log('error', $msg, $ctx); }
    public static function warn(string $msg, array $ctx = []): void  { self::log('warn',  $msg, $ctx); }
    public static function debug(string $msg, array $ctx = []): void { self::log('debug', $msg, $ctx); }
    public static function security(string $msg, array $ctx = []): void { self::log('security', $msg, $ctx); }
}
