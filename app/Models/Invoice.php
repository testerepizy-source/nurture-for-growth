<?php
namespace App\Models;
use Core\Model;

class Invoice extends Model
{
    protected static string $table = 'invoices';

    public static function forConsultation(int $consultationId, ?int $preferredAmount = null): ?array
    {
        if ($preferredAmount !== null) {
            $invoice = self::rawOne("SELECT * FROM " . static::$table . " WHERE consultation_id = ? AND amount = ? ORDER BY issued_at DESC, id DESC LIMIT 1", [$consultationId, $preferredAmount]);
            if ($invoice) {
                return $invoice;
            }
        }

        return self::rawOne("SELECT * FROM " . static::$table . " WHERE consultation_id = ? ORDER BY issued_at DESC, id DESC LIMIT 1", [$consultationId]);
    }

    public static function latestForConsultation(int $consultationId): ?array
    {
        return self::rawOne("SELECT * FROM " . static::$table . " WHERE consultation_id = ? ORDER BY issued_at DESC, id DESC LIMIT 1", [$consultationId]);
    }

    public static function findByConsultationAndAmount(int $consultationId, int $amount): ?array
    {
        return self::rawOne("SELECT * FROM " . static::$table . " WHERE consultation_id = ? AND amount = ? ORDER BY issued_at DESC LIMIT 1", [$consultationId, $amount]);
    }

    public static function generateNumber(): string
    {
        return 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    public static function detail(int $id): ?array
    {
        return self::rawOne("SELECT i.*, c.schedule_at, c.duration_minutes, c.method, c.session_type, c.provider_id, c.patient_id, c.price_amount AS consultation_price_amount,
                              c.original_price_amount, c.discount_amount, sp.name AS promo_name,
                              c.payment_method, c.payment_status, c.payment_proof_path,
                              u.name AS provider_name, pu.name AS patient_name, p.medical_record_number
                              FROM invoices i
                              JOIN consultations c ON c.id = i.consultation_id
                              LEFT JOIN service_promos sp ON sp.id = c.promo_id
                              JOIN users u ON u.id = c.provider_id
                              JOIN patients p ON p.id = i.patient_id
                              JOIN users pu ON pu.id = p.user_id
                              WHERE i.id = ?", [$id]);
    }

    public static function getReschedules(int $consultationId): array
    {
        return self::raw("SELECT * FROM consultation_reschedules 
                         WHERE original_consultation_id = ? 
                         AND status IN ('pending', 'approved', 'completed')
                         ORDER BY created_at ASC", [$consultationId]);
    }

    public static function getReschedulesForConsultation(int $consultationId, ?array $invoice = null): array
    {
        $reschedules = self::getReschedules($consultationId);
        if (!empty($reschedules) || empty($invoice)) {
            return $reschedules;
        }

        return self::raw("SELECT cr.* FROM consultation_reschedules cr
                         JOIN consultations c ON c.id = cr.original_consultation_id
                         WHERE cr.new_schedule_at = ?
                           AND c.patient_id = ?
                           AND c.provider_id = ?
                           AND cr.status IN ('approved', 'completed')
                         ORDER BY cr.created_at ASC", [
                             $invoice['schedule_at'],
                             $invoice['patient_id'],
                             $invoice['provider_id']
                         ]);
    }

    public static function getExtensions(int $consultationId): array
    {
        return self::raw("SELECT * FROM consultation_extensions 
                         WHERE consultation_id = ? 
                         AND status IN ('approved', 'completed')
                         ORDER BY created_at ASC", [$consultationId]);
    }
}
