<?php
namespace App\Models;
use Core\Model;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';

    public static function recent(int $limit = 200): array
    {
        return self::raw("SELECT al.*, u.name AS user_name FROM audit_logs al
                           LEFT JOIN users u ON u.id = al.user_id
                           ORDER BY al.created_at DESC LIMIT " . (int) $limit);
    }
}
