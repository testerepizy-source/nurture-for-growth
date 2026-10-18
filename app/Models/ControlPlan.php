<?php
namespace App\Models;
use Core\Model;

class ControlPlan extends Model
{
    protected static string $table = 'control_plans';

    public static function forPatient(int $patientId): array
    {
        return self::raw('SELECT * FROM control_plans WHERE patient_id = ? ORDER BY next_control_date DESC', [$patientId]);
    }
}
