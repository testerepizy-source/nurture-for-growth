<?php
namespace App\Models;
use Core\Model;

class Notification extends Model
{
    protected static string $table = 'notifications';

    public static function forUser(int $userId, int $limit = 10): array
    {
        return self::raw('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int) $limit, [$userId]);
    }

    public static function unreadCount(int $userId): int
    {
        $row = self::rawOne('SELECT COUNT(*) c FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
        return (int) ($row['c'] ?? 0);
    }
}
