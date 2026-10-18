<?php
namespace App\Models;
use Core\Model;

class VideoSession extends Model
{
    protected static string $table = 'video_sessions';

    public static function byConsultation(int $consultationId): ?array
    {
        return self::findBy('consultation_id', $consultationId);
    }
}
