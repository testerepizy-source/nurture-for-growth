<?php
namespace App\Models;
use Core\Model;

class FollowUpPlan extends Model
{
    protected static string $table = 'follow_up_plans';

    public static function forConsultation(int $consultationId): array
    {
        return self::raw('SELECT * FROM follow_up_plans WHERE consultation_id = ? ORDER BY created_at DESC', [$consultationId]);
    }

    public static function forPatient(int $patientId): array
    {
        return self::raw("SELECT fp.*, u.name AS provider_name, c.schedule_at
                           FROM follow_up_plans fp
                           JOIN consultations c ON c.id = fp.consultation_id
                           JOIN users u ON u.id = fp.created_by
                           WHERE c.patient_id = ? ORDER BY fp.created_at DESC", [$patientId]);
    }

    public static function forProvider(int $providerId): array
    {
        return self::raw("SELECT fp.*, pu.name AS patient_name, c.schedule_at
                           FROM follow_up_plans fp
                           JOIN consultations c ON c.id = fp.consultation_id
                           JOIN patients p ON p.id = c.patient_id
                           JOIN users pu ON pu.id = p.user_id
                           WHERE fp.created_by = ? ORDER BY fp.created_at DESC", [$providerId]);
    }

    public static function recent(int $limit = 5): array
    {
        return self::raw("SELECT fp.*, pu.name AS patient_name, u.name AS provider_name, c.schedule_at
                           FROM follow_up_plans fp
                           JOIN consultations c ON c.id = fp.consultation_id
                           JOIN patients p ON p.id = c.patient_id
                           JOIN users pu ON pu.id = p.user_id
                           JOIN users u ON u.id = fp.created_by
                           ORDER BY fp.created_at DESC LIMIT " . (int) $limit);
    }
}
