<?php
namespace App\Models;
use Core\Model;

class Faq extends Model
{
    protected static string $table = 'faqs';

    public static function published(): array
    {
        return self::raw('SELECT * FROM faqs WHERE is_published = 1 ORDER BY order_no ASC, id ASC');
    }
}
