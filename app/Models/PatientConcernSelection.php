<?php
namespace App\Models;
use Core\Model;

class PatientConcernSelection extends Model
{
    protected static string $table = 'patient_concern_selections';

    public static function forPatient(int $patientId): array
    {
        return self::raw("SELECT pcs.*, ct.name AS tag_name FROM patient_concern_selections pcs
                           JOIN concern_tags ct ON ct.id = pcs.tag_id
                           WHERE pcs.patient_id = ? ORDER BY pcs.created_at DESC", [$patientId]);
    }
}
