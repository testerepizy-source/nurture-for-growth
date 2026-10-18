<?php
namespace App\Models;
use Core\Model;

class ChatMessage extends Model
{
    protected static string $table = 'chat_messages';

    public static function forConsultation(int $consultationId): array
    {
        return self::raw("SELECT cm.*, u.name AS sender_name FROM chat_messages cm
                           JOIN users u ON u.id = cm.sender_id
                           WHERE cm.consultation_id = ? ORDER BY cm.created_at ASC", [$consultationId]);
    }
}
