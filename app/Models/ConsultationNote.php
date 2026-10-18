<?php
namespace App\Models;
use Core\Model;

class ConsultationNote extends Model
{
    protected static string $table = 'consultation_notes';

    public static function forConsultation(int $consultationId): array
    {
        return self::raw("SELECT cn.*, u.name AS author_name FROM consultation_notes cn
                           JOIN users u ON u.id = cn.author_id WHERE cn.consultation_id = ? ORDER BY cn.created_at DESC", [$consultationId]);
    }
}
