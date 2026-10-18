<?php
namespace App\Models;
use Core\Model;

class Reminder extends Model
{
    protected static string $table = 'reminders';

    public static function forPatient(int $patientId): array
    {
        return self::raw('SELECT * FROM reminders WHERE patient_id = ? ORDER BY remind_at ASC', [$patientId]);
    }
}
