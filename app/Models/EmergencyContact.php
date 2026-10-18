<?php
namespace App\Models;
use Core\Model;

class EmergencyContact extends Model
{
    protected static string $table = 'emergency_contacts';

    public static function active(): array
    {
        return self::raw('SELECT * FROM emergency_contacts WHERE is_active = 1');
    }
}
