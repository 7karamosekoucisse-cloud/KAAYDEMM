<?php
declare(strict_types=1);
namespace Core;

final class Session
{
    private static bool $started = false;

    public static function start(array $cfg = []): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }
        $cfg = array_merge([
            'name'     => 'kaaydem_session',
            'lifetime' => 7200,
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ], $cfg);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.gc_maxlifetime', (string)$cfg['lifetime']);

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $cfg['secure'],
            'httponly' => $cfg['httponly'],
            'samesite' => $cfg['samesite'],
        ]);
        session_name($cfg['name']);
        session_start();
        self::$started = true;
    }

    public static function set(string $key, mixed $value): void  { $_SESSION[$key] = $value; }
    public static function get(string $key, mixed $default = null): mixed { return $_SESSION[$key] ?? $default; }
    public static function has(string $key): bool { return isset($_SESSION[$key]); }
    public static function remove(string $key): void { unset($_SESSION[$key]); }
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        self::$started = false;
    }

    public static function regenerate(): void { session_regenerate_id(true); }
    public static function flash(string $key, mixed $value): void { $_SESSION['_flash'][$key] = $value; }
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $v = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }
    public static function hasFlash(string $key): bool { return isset($_SESSION['_flash'][$key]); }
    public static function id(): string { return session_id(); }
}
