<?php
namespace App\Models;
use Core\Model;

class MedicalNote extends Model
{
    protected static string $table = 'medical_notes';

    public static function forPatient(int $patientId): array
    {
        return self::raw("SELECT mn.*, u.name AS psychiatrist_name FROM medical_notes mn
                           JOIN users u ON u.id = mn.psychiatrist_id
                           WHERE mn.patient_id = ? ORDER BY mn.created_at DESC", [$patientId]);
    }
}
