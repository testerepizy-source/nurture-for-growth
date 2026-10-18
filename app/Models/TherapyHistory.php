<?php
namespace App\Models;
use Core\Model;

class TherapyHistory extends Model
{
    protected static string $table = 'therapy_history';

    public static function forPatient(int $patientId): array
    {
        return self::raw('SELECT * FROM therapy_history WHERE patient_id = ? ORDER BY started_at DESC', [$patientId]);
    }
}
