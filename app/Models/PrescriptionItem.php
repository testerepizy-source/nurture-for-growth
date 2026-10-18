<?php
namespace App\Models;
use Core\Model;

class PrescriptionItem extends Model
{
    protected static string $table = 'prescription_items';

    public static function forPrescription(int $prescriptionId): array
    {
        return self::raw('SELECT * FROM prescription_items WHERE prescription_id = ?', [$prescriptionId]);
    }
}
