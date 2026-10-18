<?php
namespace App\Models;
use Core\Model;

class ConsultationExtension extends Model
{
    protected static string $table = 'consultation_extensions';

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
            SELECT ce.*, c.duration_minutes AS original_duration, u.name AS provider_name
            FROM consultation_extensions ce
            JOIN consultations c ON c.id = ce.consultation_id
            JOIN users u ON u.id = c.provider_id
            WHERE c.patient_id = ?
            ORDER BY ce.created_at DESC
        ", [$patientId]);
    }

    public static function forProvider(int $providerId, string $status = ''): array
    {
        if (!static::tableExists()) {
            return [];
        }

        $sql = "
            SELECT ce.*, c.duration_minutes AS original_duration, u.name AS patient_name, p.medical_record_number
            FROM consultation_extensions ce
            JOIN consultations c ON c.id = ce.consultation_id
            JOIN patients p ON p.id = c.patient_id
            JOIN users u ON u.id = p.user_id
            WHERE c.provider_id = ?
        ";
        $params = [$providerId];
        
        if (!empty($status)) {
            $sql .= " AND ce.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY ce.created_at DESC";
        return self::raw($sql, $params);
    }

    public static function forConsultation(int $consultationId): array
    {
        if (!static::tableExists()) {
            return [];
        }

        return self::raw("
            SELECT * FROM consultation_extensions
            WHERE consultation_id = ?
            ORDER BY created_at DESC
        ", [$consultationId]);
    }

    public static function getPending(int $consultationId): ?array
    {
        if (!static::tableExists()) {
            return null;
        }

        return self::rawOne("
            SELECT * FROM consultation_extensions
            WHERE consultation_id = ? AND status = 'pending'
            ORDER BY created_at DESC LIMIT 1
        ", [$consultationId]);
    }
}
