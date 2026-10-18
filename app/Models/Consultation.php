<?php
namespace App\Models;
use Core\Model;

class Consultation extends Model
{
    protected static string $table = 'consultations';

    public static function forPatient(int $patientId): array
    {
        return self::raw("SELECT c.*, u.name AS provider_name FROM consultations c
                           JOIN users u ON u.id = c.provider_id WHERE c.patient_id = ? ORDER BY c.schedule_at DESC", [$patientId]);
    }

    public static function forProvider(int $providerId, ?string $status = null, bool $upcomingOnly = false): array
    {
        $sql = "SELECT c.*, u.name AS patient_name, p.medical_record_number FROM consultations c
                JOIN patients p ON p.id = c.patient_id
                JOIN users u ON u.id = p.user_id
                WHERE c.provider_id = ?";
        $params = [$providerId];
        if ($status) { $sql .= ' AND c.status = ?'; $params[] = $status; }
        if ($upcomingOnly) {
            $sql .= ' AND DATE_ADD(c.schedule_at, INTERVAL c.duration_minutes MINUTE) >= NOW()';
        }
        $sql .= ' ORDER BY c.schedule_at ASC';
        return self::raw($sql, $params);
    }

    public static function detail(int $id): ?array
    {
        return self::rawOne("SELECT c.*, u.name AS provider_name, pu.name AS patient_name, p.medical_record_number, p.id AS patient_id
                              FROM consultations c
                              JOIN users u ON u.id = c.provider_id
                              JOIN patients p ON p.id = c.patient_id
                              JOIN users pu ON pu.id = p.user_id
                              WHERE c.id = ?", [$id]);
    }

    public static function syncExpiredForPatientProvider(int $patientId, int $providerId): void
    {
        $latestExpired = self::rawOne(
            "SELECT schedule_at
             FROM consultations
             WHERE patient_id = ?
               AND provider_id = ?
               AND DATE_ADD(schedule_at, INTERVAL duration_minutes MINUTE) <= NOW()
               AND status IN ('approved', 'rescheduled', 'completed')
             ORDER BY schedule_at DESC
             LIMIT 1",
            [$patientId, $providerId]
        );

        if (!$latestExpired) {
            self::rawExecute(
                "DELETE FROM consultations
                 WHERE patient_id = ?
                   AND provider_id = ?
                   AND DATE_ADD(schedule_at, INTERVAL duration_minutes MINUTE) <= NOW()
                   AND status IN ('pending', 'approved', 'rescheduled', 'completed')",
                [$patientId, $providerId]
            );
            return;
        }

        self::rawExecute(
            "UPDATE consultations
             SET status = 'completed'
             WHERE patient_id = ?
               AND provider_id = ?
               AND status IN ('approved', 'rescheduled')
               AND schedule_at = ?",
            [$patientId, $providerId, $latestExpired['schedule_at']]
        );

        self::rawExecute(
            "DELETE FROM consultations
             WHERE patient_id = ?
               AND provider_id = ?
               AND DATE_ADD(schedule_at, INTERVAL duration_minutes MINUTE) <= NOW()
               AND status IN ('pending', 'approved', 'rescheduled', 'completed')
               AND schedule_at < ?",
            [$patientId, $providerId, $latestExpired['schedule_at']]
        );
    }
}
