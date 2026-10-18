<?php
namespace App\Models;
use Core\Model;

class LoginHistory extends Model
{
    protected static string $table = 'login_history';

    public static function forUser(int $userId): array
    {
        return self::raw('SELECT * FROM login_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 50', [$userId]);
    }
}
