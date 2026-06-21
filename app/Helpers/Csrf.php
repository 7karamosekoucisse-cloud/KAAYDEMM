<?php
declare(strict_types=1);
namespace App\Helpers;

use Core\Session;
use App\Exceptions\CsrfException;

final class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf')) {
            Session::set('_csrf', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf');
    }

    public static function field(): string
    {
        $t = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return "<input type=\"hidden\" name=\"_csrf\" value=\"{$t}\">";
    }

    public static function check(?string $token): void
    {
        $expected = Session::get('_csrf');
        if (!$expected || !$token || !hash_equals($expected, $token)) {
            throw new CsrfException();
        }
    }

    public static function verify(array $post): void
    {
        self::check($post['_csrf'] ?? null);
    }
}
