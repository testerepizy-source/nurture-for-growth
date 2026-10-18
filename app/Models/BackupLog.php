<?php
namespace App\Models;
use Core\Model;

class BackupLog extends Model
{
    protected static string $table = 'backup_logs';

    public static function recent(int $limit = 50): array
    {
        return self::raw('SELECT * FROM backup_logs ORDER BY created_at DESC LIMIT ' . (int) $limit);
    }
}
