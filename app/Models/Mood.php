<?php
namespace App\Models;
use Core\Model;

class Mood extends Model
{
    protected static string $table = 'moods';

    public static function forPatient(int $patientId, int $limit = 60): array
    {
        return self::raw('SELECT * FROM moods WHERE patient_id = ? ORDER BY mood_date DESC LIMIT ' . (int) $limit, [$patientId]);
    }

    public static function findByPatientAndDate(int $patientId, string $date): ?array
    {
        return self::rawOne('SELECT * FROM moods WHERE patient_id = ? AND mood_date = ?', [$patientId, $date]);
    }

    public static function weeklyAverage(int $patientId): array
    {
        return self::raw("SELECT mood_date, mood_scale FROM moods WHERE patient_id = ? AND mood_date >= (CURDATE() - INTERVAL 7 DAY) ORDER BY mood_date ASC", [$patientId]);
    }

    public static function monthlyAverage(int $patientId): array
    {
        return self::raw("SELECT mood_date, mood_scale FROM moods WHERE patient_id = ? AND mood_date >= (CURDATE() - INTERVAL 30 DAY) ORDER BY mood_date ASC", [$patientId]);
    }
}
