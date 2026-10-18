<?php
namespace App\Models;
use Core\Model;

class Service extends Model
{
    protected static string $table = 'services';

    public static function active(): array
    {
        return self::raw('SELECT * FROM services WHERE is_active = 1 ORDER BY order_no ASC, id ASC');
    }
}
