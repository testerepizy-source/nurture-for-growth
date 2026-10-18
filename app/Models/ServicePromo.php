<?php
namespace App\Models;
use Core\Model;

class ServicePromo extends Model
{
    protected static string $table = 'service_promos';

    /** Promo yang sedang berjalan (periode mencakup hari ini) untuk banner frontend. */
    public static function runningNow(?int $providerId = null): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT sp.*, u.name AS provider_name
                FROM service_promos sp
                LEFT JOIN users u ON u.id = sp.provider_id
                WHERE sp.is_active = 1 AND sp.start_date <= ? AND sp.end_date >= ?";
        $params = [$today, $today];
        if ($providerId !== null) {
            $sql .= " AND (sp.scope = 'all' OR (sp.scope = 'provider' AND sp.provider_id = ?))";
            $params[] = $providerId;
        }
        $sql .= " ORDER BY sp.scope ASC, sp.discount_value DESC, sp.id DESC";
        return self::raw($sql, $params);
    }

    /** Promo terbaik (diskon terbesar) yang aktif untuk provider pada suatu tanggal. */
    public static function activeFor(int $providerId, string $date): ?array
    {
        $sql = "SELECT * FROM service_promos
                WHERE is_active = 1
                  AND start_date <= ?
                  AND end_date >= ?
                  AND (scope = 'all' OR (scope = 'provider' AND provider_id = ?))
                ORDER BY id DESC";
        $rows = self::raw($sql, [$date, $date, $providerId]);
        if (!$rows) return null;

        usort($rows, fn($a, $b) => self::discountAmountFor($b, 1000000) <=> self::discountAmountFor($a, 1000000));
        return $rows[0];
    }

    /** Besar potongan untuk harga dasar tertentu. */
    public static function discountAmountFor(array $promo, int $basePrice): int
    {
        $amount = $promo['discount_type'] === 'percent'
            ? (int) round($basePrice * min((int) $promo['discount_value'], 100) / 100)
            : (int) $promo['discount_value'];
        return max(0, min($amount, $basePrice));
    }

    public static function finalPrice(int $basePrice, array $promo): int
    {
        return max(0, $basePrice - self::discountAmountFor($promo, $basePrice));
    }

    public static function withProvider(): array
    {
        return self::raw("SELECT sp.*, u.name AS provider_name
                          FROM service_promos sp
                          LEFT JOIN users u ON u.id = sp.provider_id
                          ORDER BY sp.is_active DESC, sp.start_date DESC, sp.id DESC");
    }

    public static function forProvider(int $providerId): array
    {
        return self::raw("SELECT * FROM service_promos WHERE provider_id = ? ORDER BY start_date DESC, id DESC", [$providerId]);
    }

    public static function activeBetween(string $fromDate, string $toDate, ?int $providerId = null): array
    {
        $sql = "SELECT * FROM service_promos
                WHERE is_active = 1 AND start_date <= ? AND end_date >= ?";
        $params = [$toDate, $fromDate];
        if ($providerId !== null) {
            $sql .= " AND (scope = 'all' OR (scope = 'provider' AND provider_id = ?))";
            $params[] = $providerId;
        }
        $sql .= " ORDER BY scope ASC, start_date ASC, id ASC";
        return self::raw($sql, $params);
    }
}
