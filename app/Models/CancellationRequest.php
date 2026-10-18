<?php
namespace App\Models;
use Core\Model;

class CancellationRequest extends Model
{
    protected static string $table = 'cancellation_requests';

    public static function forConsultation(int $consultationId): ?array
    {
        return self::findBy('consultation_id', $consultationId);
    }

    public static function pendingList(?int $providerId = null): array
    {
        $sql = "SELECT cr.*, u.name AS requested_by_name, pu.name AS patient_name,
                       c.schedule_at, c.price_amount, prov.name AS provider_name
                       FROM cancellation_requests cr
                       JOIN consultations c ON c.id = cr.consultation_id
                       JOIN patients p ON p.id = c.patient_id
                       JOIN users pu ON pu.id = p.user_id
                       JOIN users prov ON prov.id = c.provider_id
                       JOIN users u ON u.id = cr.requested_by
                       WHERE cr.status = 'pending'";
        $args = [];
        if ($providerId) {
            $sql .= " AND c.provider_id = ?";
            $args[] = $providerId;
        }
        $sql .= " ORDER BY cr.created_at ASC";
        return self::raw($sql, $args);
    }
}
