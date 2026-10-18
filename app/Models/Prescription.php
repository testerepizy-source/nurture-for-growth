<?php
namespace App\Models;
use Core\Model;

class Prescription extends Model
{
    protected static string $table = 'prescriptions';

    public static function generateNumber(): string
    {
        return 'RX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    public static function forPatient(int $patientId): array
    {
        return self::raw("SELECT rx.*, u.name AS psychiatrist_name FROM prescriptions rx
                           JOIN users u ON u.id = rx.psychiatrist_id
                           WHERE rx.patient_id = ? ORDER BY rx.created_at DESC", [$patientId]);
    }

    public static function detail(int $id): ?array
    {
        return self::rawOne("SELECT rx.*, u.name AS psychiatrist_name, psy.str_number, psy.sip_number,
                              pu.name AS patient_name, p.medical_record_number, p.birth_date
                              FROM prescriptions rx
                              JOIN users u ON u.id = rx.psychiatrist_id
                              JOIN psychiatrists psy ON psy.user_id = rx.psychiatrist_id
                              JOIN patients p ON p.id = rx.patient_id
                              JOIN users pu ON pu.id = p.user_id
                              WHERE rx.id = ?", [$id]);
    }
}
