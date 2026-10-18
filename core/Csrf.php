<?php

namespace Core;

class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES);
        return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
    }

    public static function verify(?string $token): bool
    {
        return $token !== null && Session::has('_csrf_token') && hash_equals(Session::get('_csrf_token'), $token);
    }

    public static function verifyRequest(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') return true;
        return self::verify($_POST['_csrf'] ?? null);
    }
}
