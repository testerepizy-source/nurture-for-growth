<?php
namespace App\Models;
use Core\Model;

class SessionAttendance extends Model
{
    protected static string $table = 'session_attendance';

    public static function forSession(int $videoSessionId): array
    {
        return self::raw("SELECT sa.*, u.name FROM session_attendance sa
                           JOIN users u ON u.id = sa.user_id WHERE sa.video_session_id = ?", [$videoSessionId]);
    }

    public static function hasJoined(int $videoSessionId, int $userId): bool
    {
        return self::rawOne(
            'SELECT id FROM session_attendance WHERE video_session_id = ? AND user_id = ? LIMIT 1',
            [$videoSessionId, $userId]
        ) !== null;
    }
}
