<?php
namespace App\Models;
use Core\Model;

class PaymentTransaction extends Model
{
    protected static string $table = 'payment_transactions';

    public static function forPatient(int $patientId): array
    {
        return self::raw("
            SELECT * FROM payment_transactions
            WHERE patient_id = ?
            ORDER BY created_at DESC
        ", [$patientId]);
    }

    public static function forConsultation(int $consultationId): array
    {
        return self::raw("
            SELECT * FROM payment_transactions
            WHERE reference_type = 'consultation' AND reference_id = ?
            ORDER BY created_at DESC
        ", [$consultationId]);
    }

    public static function forReschedule(int $rescheduleId): array
    {
        return self::raw("
            SELECT * FROM payment_transactions
            WHERE reference_type = 'reschedule' AND reference_id = ?
            ORDER BY created_at DESC
        ", [$rescheduleId]);
    }

    public static function forExtension(int $extensionId): array
    {
        return self::raw("
            SELECT * FROM payment_transactions
            WHERE reference_type = 'extension' AND reference_id = ?
            ORDER BY created_at DESC
        ", [$extensionId]);
    }

    public static function getPaid(string $referenceType, int $referenceId): ?array
    {
        return self::rawOne("
            SELECT * FROM payment_transactions
            WHERE reference_type = ? AND reference_id = ? AND payment_status = 'paid'
            ORDER BY created_at DESC LIMIT 1
        ", [$referenceType, $referenceId]);
    }

    public static function latestPending(string $referenceType, int $referenceId): ?array
    {
        $environment = env('MIDTRANS_IS_PRODUCTION', '0') === '1' ? 'production' : 'sandbox';
        $environmentNote = 'midtrans_environment:' . $environment;
        return self::rawOne("SELECT * FROM payment_transactions WHERE reference_type = ? AND reference_id = ? AND payment_method = 'midtrans' AND payment_status = 'pending_verification' AND notes = ? ORDER BY created_at DESC LIMIT 1", [$referenceType, $referenceId, $environmentNote]);
    }
}
