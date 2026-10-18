<?php
namespace App\Models;
use Core\Model;

class ConsultationReschedule extends Model
{
    protected static string $table = 'consultation_reschedules';

    public static function find($id): ?array
    {
        if (!static::tableExists()) {
            return null;
        }

        return parent::find($id);
    }

    public static function create(array $data): int
    {
        if (!static::tableExists()) {
            return 0;
        }

        return parent::create($data);
    }

    public static function update($id, array $data): bool
    {
        if (!static::tableExists()) {
            return false;
        }

        return parent::update($id, $data);
    }

    public static function forPatient(int $patientId): array
    {
        if (!static::tableExists()) {
            return [];
        }

        return self::raw("
            SELECT cr.*, c.schedule_at AS original_schedule, u.name AS provider_name
            FROM consultation_reschedules cr
            JOIN consultations c ON c.id = cr.original_consultation_id
            JOIN users u ON u.id = c.provider_id
            WHERE c.patient_id = ?
            ORDER BY cr.created_at DESC
        ", [$patientId]);
    }

    public static function forProvider(int $providerId, string $status = ''): array
    {
        if (!static::tableExists()) {
            return [];
        }

        $sql = "
            SELECT cr.*, c.schedule_at AS original_schedule, u.name AS patient_name, p.medical_record_number
            FROM consultation_reschedules cr
            JOIN consultations c ON c.id = cr.original_consultation_id
            JOIN patients p ON p.id = c.patient_id
            JOIN users u ON u.id = p.user_id
            WHERE c.provider_id = ?
        ";
        $params = [$providerId];
        
        if (!empty($status)) {
            $sql .= " AND cr.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY cr.created_at DESC";
        return self::raw($sql, $params);
    }

    public static function getPending(int $consultationId): ?array
    {
        if (!static::tableExists()) {
            return null;
        }

        return self::rawOne("
            SELECT * FROM consultation_reschedules
            WHERE original_consultation_id = ? AND status = 'pending'
            ORDER BY created_at DESC LIMIT 1
        ", [$consultationId]);
    }

    public static function getLastCompleted(int $consultationId): ?array
    {
        if (!static::tableExists()) {
            return null;
        }

        return self::rawOne("
            SELECT * FROM consultation_reschedules
            WHERE original_consultation_id = ? AND status IN ('approved', 'completed')
            ORDER BY created_at DESC LIMIT 1
        ", [$consultationId]);
    }

    public static function getEffectiveScheduleAt(int $consultationId, ?string $defaultScheduleAt = null): ?string
    {
        $completed = self::getLastCompleted($consultationId);
        return $completed['new_schedule_at'] ?? $defaultScheduleAt;
    }

    public static function isDuplicateTargetConsultation(array $consultation): bool
    {
        if (!static::tableExists()) {
            return false;
        }

        if (empty($consultation['schedule_at']) || empty($consultation['patient_id']) || empty($consultation['provider_id'])) {
            return false;
        }

        $duplicate = self::rawOne(
            "SELECT 1 FROM consultation_reschedules cr
             JOIN consultations c ON c.id = cr.original_consultation_id
             WHERE cr.new_schedule_at = ?
               AND c.patient_id = ?
               AND c.provider_id = ?
               AND cr.status IN ('approved', 'completed')
               AND cr.original_consultation_id != ?
             LIMIT 1",
            [
                $consultation['schedule_at'],
                $consultation['patient_id'],
                $consultation['provider_id'],
                $consultation['id'],
            ]
        );

        return !empty($duplicate);
    }

    public static function findOriginalConsultationIdForDuplicateTarget(array $consultation): ?int
    {
        if (!static::tableExists()) {
            return null;
        }

        if (empty($consultation['schedule_at']) || empty($consultation['patient_id']) || empty($consultation['provider_id'])) {
            return null;
        }

        $row = self::rawOne(
            "SELECT cr.original_consultation_id FROM consultation_reschedules cr
             JOIN consultations c ON c.id = cr.original_consultation_id
             WHERE cr.new_schedule_at = ?
               AND c.patient_id = ?
               AND c.provider_id = ?
               AND cr.status IN ('approved', 'completed')
               AND cr.original_consultation_id != ?
             ORDER BY cr.created_at DESC LIMIT 1",
            [
                $consultation['schedule_at'],
                $consultation['patient_id'],
                $consultation['provider_id'],
                $consultation['id'],
            ]
        );

        return $row['original_consultation_id'] ?? null;
    }
}
