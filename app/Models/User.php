<?php
namespace App\Models;
use Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function withRole(): array
    {
        return self::raw("SELECT u.*, r.label AS role_label, r.name AS role_name
                           FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id DESC");
    }
}
