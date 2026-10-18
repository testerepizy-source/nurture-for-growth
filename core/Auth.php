<?php

namespace Core;

use App\Models\User;
use App\Models\Role;

class Auth
{
    public static function attempt(string $email, string $password): ?array
    {
        $user = User::findBy('email', $email);
        if (!$user) return null;
        if ($user['status'] !== 'active') return null;
        if (empty($user['email_verified_at'])) return null;
        if (!password_verify($password, $user['password'])) return null;
        return $user;
    }

    public static function login(array $user, bool $remember = false): void
    {
        Session::set('user_id', $user['id']);
        Session::set('user_role_id', $user['role_id']);
        session_regenerate_id(true);

        User::update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            User::update($user['id'], ['remember_token' => $token]);
            setcookie('nfg_remember', $user['id'] . '|' . $token, time() + (30 * 86400), '/', '', false, true);
        }
    }

    public static function logout(): void
    {
        setcookie('nfg_remember', '', time() - 3600, '/');
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        static $cached = null;
        if (!self::check()) return null;
        if ($cached === null) {
            $cached = User::find(self::id());
        }
        return $cached;
    }

    public static function roleName(): ?string
    {
        $user = self::user();
        if (!$user) return null;
        $role = Role::find($user['role_id']);
        return $role['name'] ?? null;
    }

    public static function can(string $permissionCode): bool
    {
        $user = self::user();
        if (!$user) return false;
        // super_admin always allowed
        if (self::roleName() === 'super_admin') return true;

        $sql = "SELECT COUNT(*) c FROM role_permission rp
                JOIN permissions p ON p.id = rp.permission_id
                WHERE rp.role_id = ? AND p.code = ?";
        $row = \App\Models\User::rawOne($sql, [$user['role_id'], $permissionCode]);
        return $row && (int) $row['c'] > 0;
    }
}
