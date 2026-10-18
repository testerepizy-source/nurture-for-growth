<?php
namespace App\Models;
use Core\Model;

class AiSummaryLog extends Model
{
    protected static string $table = 'ai_summary_logs';

    public static function forSubject(string $type, int $id): array
    {
        return self::raw('SELECT * FROM ai_summary_logs WHERE subject_type = ? AND subject_id = ? ORDER BY created_at DESC', [$type, $id]);
    }
}
