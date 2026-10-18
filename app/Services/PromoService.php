<?php
namespace App\Services;

use App\Models\ServicePromo;

/**
 * Promo/diskon layanan konseling.
 * Promo dibuat oleh psikolog/psikiater (untuk layanan sendiri) atau admin
 * (untuk semua provider atau provider tertentu), berlaku selama periode bulan
 * tertentu dan otomatis dipotongkan saat booking dibuat.
 */
class PromoService
{
    /** Ambil promo aktif untuk provider pada tanggal sesi (YYYY-MM-DD). */
    public static function resolve(int $providerId, string $scheduleDate): ?array
    {
        return ServicePromo::activeFor($providerId, $scheduleDate);
    }

    /** Terapkan diskon: mengembalikan [hargaAkhir, besarPotongan]. */
    public static function apply(int $basePrice, ?array $promo): array
    {
        if (!$promo) return [$basePrice, 0];
        $final = ServicePromo::finalPrice($basePrice, $promo);
        return [$final, $basePrice - $final];
    }

    /** Label singkat promo, misal: "Diskon Awal Tahun (-20%)" / "Promo Gajian (-Rp50.000)". */
    public static function label(?array $promo): string
    {
        if (!$promo) return '';
        return $promo['discount_type'] === 'percent'
            ? sprintf('%s (-%d%%)', $promo['name'], (int) $promo['discount_value'])
            : sprintf('%s (-Rp%s)', $promo['name'], number_format((int) $promo['discount_value'], 0, ',', '.'));
    }

    /** Daftar promo aktif dalam rentang tanggal (untuk banner form booking). */
    public static function activeForBookingWindow(?int $providerId, int $daysAhead = 60): array
    {
        return ServicePromo::activeBetween(date('Y-m-d'), date('Y-m-d', strtotime("+{$daysAhead} days")), $providerId);
    }
}
