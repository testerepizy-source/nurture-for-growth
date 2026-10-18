<?php
namespace App\Models;

use Core\Model;

class InformedConsent extends Model
{
    protected static string $table = 'informed_consents';

    public static function forPatient(int $patientId): ?array
    {
        return self::rawOne(
            'SELECT * FROM informed_consents WHERE patient_id = ? ORDER BY signed_at DESC, id DESC LIMIT 1',
            [$patientId]
        );
    }
}
