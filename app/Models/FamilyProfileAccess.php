<?php
namespace App\Models;

use Core\Model;

class FamilyProfileAccess extends Model
{
    protected static string $table = 'family_profile_access';

    public static function forUser(int $userId): array
    {
        return self::raw("SELECT fpa.*, p.id AS patient_id, p.medical_record_number,
                                 pu.name AS patient_name, pu.photo AS patient_photo,
                                 pu.gender, pu.birth_date
                          FROM family_profile_access fpa
                          JOIN patients p ON p.id = fpa.patient_id
                          JOIN users pu ON pu.id = p.user_id
                          WHERE fpa.user_id = ? AND fpa.status = 'active'
                          ORDER BY fpa.is_primary DESC, pu.name ASC", [$userId]);
    }

    public static function ensurePrimaryProfile(int $userId): ?array
    {
        $patient = Patient::byUserId($userId);
        if (!$patient) return null;

        self::rawExecute("INSERT IGNORE INTO family_profile_access
                          (user_id, patient_id, relationship, is_primary, can_manage, can_view_medical_data)
                          VALUES (?, ?, 'self', 1, 1, 1)", [$userId, $patient['id']]);
        return $patient;
    }

    public static function canAccess(int $userId, int $patientId): bool
    {
        return (bool) self::rawOne("SELECT id FROM family_profile_access
                                    WHERE user_id = ? AND patient_id = ? AND status = 'active'",
                                   [$userId, $patientId]);
    }

    public static function accessFor(int $userId, int $patientId): ?array
    {
        return self::rawOne("SELECT * FROM family_profile_access
                             WHERE user_id = ? AND patient_id = ? AND status = 'active' LIMIT 1",
                            [$userId, $patientId]);
    }
}
