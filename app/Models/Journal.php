<?php
namespace App\Models;
use Core\Model;

class Journal extends Model
{
    protected static string $table = 'journals';

    public static function forPatient(int $patientId, string $search = ''): array
    {
        if ($search !== '') {
            $like = '%' . $search . '%';
            return self::raw('SELECT * FROM journals WHERE patient_id = ? AND (title LIKE ? OR content LIKE ? OR tag LIKE ?) ORDER BY journal_date DESC', [$patientId, $like, $like, $like]);
        }
        return self::raw('SELECT * FROM journals WHERE patient_id = ? ORDER BY journal_date DESC, id DESC', [$patientId]);
    }
}
