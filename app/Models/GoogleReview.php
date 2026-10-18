<?php

namespace App\Models;

use Core\Model;

class GoogleReview extends Model
{
    protected static string $table = 'google_reviews';

    /** Ulasan aktif untuk landing (urut manual lalu terbaru). */
    public static function active(int $limit = 6): array
    {
        return self::raw('SELECT * FROM google_reviews WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT ' . (int) $limit);
    }
}
