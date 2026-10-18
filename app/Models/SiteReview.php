<?php
namespace App\Models;
use Core\Model;

class SiteReview extends Model
{
    protected static string $table = 'site_reviews';

    public static function published(): array
    {
        return self::raw('SELECT sr.*, u.name AS user_name FROM site_reviews sr JOIN users u ON u.id = sr.user_id WHERE sr.is_published = 1 ORDER BY sr.created_at DESC');
    }
}
