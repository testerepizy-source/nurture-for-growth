<?php
namespace App\Services;
use App\Models\Consultation;
use App\Models\ConsultationReschedule;
use App\Models\ConsultationExtension;
use App\Models\Invoice;
use App\Models\Psychologist;
use App\Models\Psychiatrist;

class ConsultationModificationService
{
    public const RESCHEDULE_GRACE_PERIOD_HOURS = 24;
    public const LATE_RESCHEDULE_FEE = 100000;

    // Extension price per 15 minutes (will be calculated from tier)
    private const EXTENSION_PRICE_PER_15_MIN = 0;

    /**
     * Calculate rescheduling fee
     */
    public static function rescheduleFeePercentage(): int
    {
        return 0;
    }

    public static function calculateRescheduleFee(int $consultationId): int
    {
        $consultation = Consultation::find($consultationId);
        if (!$consultation) return 0;

        $hoursBefore = (strtotime($consultation['schedule_at']) - time()) / 3600;
        return $hoursBefore >= self::RESCHEDULE_GRACE_PERIOD_HOURS ? 0 : self::LATE_RESCHEDULE_FEE;
    }

    public static function hoursUntilConsultation(array $consultation): float
    {
        return max(0, (strtotime($consultation['schedule_at']) - time()) / 3600);
    }

    /**
     * Calculate extension price based on provider tier and extension minutes
     */
    public static function calculateExtensionPrice(int $consultationId, int $extensionMinutes): int
    {
        $consultation = Consultation::find($consultationId);
        if (!$consultation) return 0;
 
        $currentDuration = max(1, (int)$consultation['duration_minutes']);
        $basePrice = (int)$consultation['price_amount'];
        $pricePerMin = $basePrice / $currentDuration;
 
        return (int)ceil($pricePerMin * $extensionMinutes);
    }

    /**
     * Request rescheduling
     */
    public static function requestReschedule(
        int $consultationId,
        string $newScheduleAt,
        string $reason = '',
        int $requestedByUserId = 0
    ): ?array {
        $consultation = Consultation::find($consultationId);
        if (!$consultation) return null;
        if (!ConsultationReschedule::tableExists()) return null;

        $fee = self::calculateRescheduleFee($consultationId);

        $rescheduleId = ConsultationReschedule::create([
            'original_consultation_id' => $consultationId,
            'new_schedule_at' => $newScheduleAt,
            'rescheduling_fee' => $fee,
            'reason' => $reason,
            'status' => 'pending',
            'requested_by' => $requestedByUserId,
            'payment_status' => 'unpaid',
        ]);

        return ConsultationReschedule::find($rescheduleId);
    }

    /**
     * Request extension
     */
    public static function requestExtension(
        int $consultationId,
        int $extensionMinutes,
        string $reason = '',
        int $requestedByUserId = 0
    ): ?array {
        $consultation = Consultation::find($consultationId);
        if (!$consultation) return null;
        if (!ConsultationExtension::tableExists()) return null;

        $currentDuration = (int)$consultation['duration_minutes'];
        $newDuration = $currentDuration + $extensionMinutes;
        $extensionPrice = self::calculateExtensionPrice($consultationId, $extensionMinutes);

        $extensionId = ConsultationExtension::create([
            'consultation_id' => $consultationId,
            'extension_minutes' => $extensionMinutes,
            'extension_price' => $extensionPrice,
            'total_new_duration' => $newDuration,
            'reason' => $reason,
            'status' => 'pending',
            'requested_by' => $requestedByUserId,
            'payment_status' => 'unpaid',
        ]);

        return ConsultationExtension::find($extensionId);
    }

    /**
     * Approve rescheduling by updating the original consultation record.
     */
    public static function approveReschedule(int $rescheduleId, int $approvedByUserId = 0): ?int
    {
        $reschedule = ConsultationReschedule::find($rescheduleId);
        if (!$reschedule || $reschedule['status'] !== 'pending') return null;

        // Check payment status
        if ($reschedule['payment_status'] !== 'paid' && $reschedule['rescheduling_fee'] > 0) {
            return null; // Payment must be done first
        }

        $original = Consultation::find($reschedule['original_consultation_id']);
        if (!$original) return null;

        $updatedPrice = ((int) $original['price_amount'] ?? 0) + ((int) $reschedule['rescheduling_fee'] ?? 0);
        $updateData = [
            'schedule_at' => $reschedule['new_schedule_at'],
            'price_amount' => $original['price_amount'],
            'payment_method' => $original['payment_method'] ?? $reschedule['payment_method'],
            'payment_status' => 'paid',
        ];
        if (!empty($reschedule['payment_proof_path'])) {
            $updateData['payment_proof_path'] = $reschedule['payment_proof_path'];
        }

        Consultation::update($original['id'], $updateData);

        $invoice = Invoice::forConsultation($original['id']);
        if ($invoice) {
            Invoice::update($invoice['id'], ['amount' => $updatedPrice]);
        } elseif ($updatedPrice > 0) {
            Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'consultation_id' => $original['id'],
                'patient_id' => $original['patient_id'],
                'amount' => $updatedPrice,
            ]);
        }

        // Mark reschedule as completed
        ConsultationReschedule::update($rescheduleId, [
            'status' => 'completed',
            'approved_by' => $approvedByUserId,
        ]);

        return $original['id'];
    }

    /**
     * Approve extension
     */
    public static function approveExtension(int $extensionId, int $approvedByUserId = 0): bool
    {
        $extension = ConsultationExtension::find($extensionId);
        if (!$extension || $extension['status'] !== 'pending') return false;

        // Check payment status
        if ($extension['payment_status'] !== 'paid' && $extension['extension_price'] > 0) {
            return false; // Payment must be done first
        }

        $consultation = Consultation::find($extension['consultation_id']);
        if (!$consultation) return false;

        // Update consultation duration and price
        $newPrice = ((int)$consultation['price_amount'] ?? 0) + $extension['extension_price'];
        Consultation::update($consultation['id'], [
            'duration_minutes' => $extension['total_new_duration'],
            'price_amount' => $newPrice,
        ]);

        // Update existing invoice total if present; otherwise generate one for a paid consultation
        $invoice = Invoice::forConsultation($consultation['id']);
        if ($invoice) {
            Invoice::update($invoice['id'], ['amount' => $newPrice]);
        } elseif ($newPrice > 0 && $consultation['payment_status'] === 'paid') {
            Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'consultation_id' => $consultation['id'],
                'patient_id' => $consultation['patient_id'],
                'amount' => $newPrice,
            ]);
        }

        // Mark extension as completed
        ConsultationExtension::update($extensionId, [
            'status' => 'completed',
            'approved_by' => $approvedByUserId,
        ]);

        return true;
    }

    /**
     * Record payment for reschedule or extension
     */
    public static function recordPayment(
        string $referenceType,
        int $referenceId,
        int $amount,
        string $paymentMethod,
        int $patientId,
        string $proofPath = ''
    ): bool {
        if ($referenceType === 'reschedule') {
            ConsultationReschedule::update($referenceId, [
                'payment_status' => 'pending_verification',
                'payment_proof_path' => $proofPath,
                'payment_method' => $paymentMethod,
            ]);
        } elseif ($referenceType === 'extension') {
            ConsultationExtension::update($referenceId, [
                'payment_status' => 'pending_verification',
                'payment_proof_path' => $proofPath,
                'payment_method' => $paymentMethod,
            ]);
        }

        return true;
    }

    /**
     * Verify payment
     */
    public static function verifyPayment(
        string $referenceType,
        int $referenceId,
        int $verifiedByUserId = 0
    ): bool {
        if ($referenceType === 'reschedule') {
            ConsultationReschedule::update($referenceId, [
                'payment_status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($referenceType === 'extension') {
            ConsultationExtension::update($referenceId, [
                'payment_status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }
}
