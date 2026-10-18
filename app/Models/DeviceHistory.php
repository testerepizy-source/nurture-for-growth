<?php
namespace App\Models;
use Core\Model;

class DeviceHistory extends Model
{
    protected static string $table = 'device_history';

    public static function forUser(int $userId): array
    {
        return self::raw('SELECT * FROM device_history WHERE user_id = ? ORDER BY last_active_at DESC', [$userId]);
    }
}
