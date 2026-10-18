<?php
namespace App\Models;
use Core\Model;

class Partner extends Model
{
    protected static string $table = 'partners';

    public static function activeByCategory(string $category): array
    {
        return self::raw('SELECT * FROM partners WHERE is_active = 1 AND category = ? ORDER BY order_no ASC, id ASC', [$category]);
    }
}
