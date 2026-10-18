<?php
namespace App\Models;
use Core\Model;

class HeroSlide extends Model
{
    protected static string $table = 'hero_slides';

    public static function active(): array
    {
        return self::raw('SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY order_no ASC, id ASC');
    }

    public static function allOrdered(): array
    {
        return self::raw('SELECT * FROM hero_slides ORDER BY order_no ASC, id ASC');
    }
}
