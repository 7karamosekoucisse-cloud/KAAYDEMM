<?php
declare(strict_types=1);

/**
 * Retourne la base URL de l'application de façon dynamique.
 * Fonctionne que ce soit à la racine ou dans un sous-dossier XAMPP.
 */
function base_url(): string
{
    static $base = null;
    if ($base !== null) return $base;

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // SCRIPT_NAME = /kaaydem/public/index.php  → base = /kaaydem/public
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir    = rtrim(dirname($script), '/');

    $base = $scheme . '://' . $host . $dir;
    return $base;
}

if (!function_exists('e')) {
    function e(?string $v): string
    {
        return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return base_url() . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        return base_url() . '/' . ltrim($path, '/');
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $default = null): mixed
    {
        return \Core\Session::getFlash($key, $default);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return \Core\Session::get('_old.' . $key, $default);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \App\Helpers\Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Helpers\Csrf::token();
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice(float $p): string
    {
        return number_format($p, 0, ',', ' ') . ' FCFA';
    }
}

if (!function_exists('formatDate')) {
    function formatDate(?string $date, string $fmt = 'd/m/Y H:i'): string
    {
        if (!$date) return '—';
        try {
            return (new \DateTimeImmutable($date))->format($fmt);
        } catch (\Throwable) {
            return '—';
        }
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(?string $date): string
    {
        if (!$date) return '—';
        try {
            $d    = new \DateTimeImmutable($date);
            $diff = (new \DateTimeImmutable())->getTimestamp() - $d->getTimestamp();
            if ($diff < 60)     return 'à l\'instant';
            if ($diff < 3600)   return (int)($diff / 60) . ' min';
            if ($diff < 86400)  return (int)($diff / 3600) . ' h';
            if ($diff < 604800) return (int)($diff / 86400) . ' j';
            return $d->format('d/m/Y');
        } catch (\Throwable) {
            return '—';
        }
    }
}

if (!function_exists('stars')) {
    function stars(float $rating, bool $html = true): string
    {
        $full  = (int)floor($rating);
        $half  = ($rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;
        if (!$html) {
            return str_repeat('★', $full) . str_repeat('☆', 5 - $full);
        }
        return str_repeat('<i class="bi bi-star-fill text-warning"></i>', $full)
             . str_repeat('<i class="bi bi-star-half text-warning"></i>', $half)
             . str_repeat('<i class="bi bi-star text-secondary"></i>', $empty);
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $max = 100): string
    {
        return strlen($text) <= $max ? $text : substr($text, 0, $max) . '…';
    }
}

if (!function_exists('authUser')) {
    function authUser(): ?\App\Models\User
    {
        static $user = null;
        if ($user !== null) return $user;
        $uid = \Core\Session::get('user_id');
        if (!$uid) return null;
        try {
            $container = $GLOBALS['container'] ?? null;
            if ($container) {
                $repo = $container->get(\App\Interfaces\UserRepositoryInterface::class);
                $user = $repo->findWithRoles((int)$uid);
            }
        } catch (\Throwable) {}
        return $user;
    }
}

if (!function_exists('isAuth')) {
    function isAuth(): bool
    {
        return \Core\Session::has('user_id');
    }
}

if (!function_exists('hasRole')) {
    function hasRole(string $role): bool
    {
        $u = authUser();
        return $u && $u->hasApprovedRole(\App\Enums\Role::from($role));
    }
}

if (!function_exists('backButton')) {
    function backButton(string $label = 'Retour', ?string $url = null): string
    {
        $href = $url ?? 'javascript:history.back()';
        $lbl  = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        return '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '" class="kd-back-btn mb-4 d-inline-flex">' . "<div class=\"kd-back-arrow\"><i class=\"bi bi-arrow-left\"></i></div><span>$lbl</span>" . '</a>';
    }
}
