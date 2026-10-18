<?php
namespace App\Models;
use Core\Model;

class ContactMessage extends Model
{
    protected static string $table = 'contact_messages';

    public static function recent(int $limit = 200): array
    {
        return self::raw("SELECT cm.*, u.name AS preferred_provider_name FROM contact_messages cm
                           LEFT JOIN users u ON u.id = cm.preferred_provider_id
                           ORDER BY cm.created_at DESC LIMIT " . (int) $limit);
    }
}
