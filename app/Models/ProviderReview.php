<?php
namespace App\Models;
use Core\Model;

class ProviderReview extends Model
{
    protected static string $table = 'provider_reviews';

    public static function forProvider(int $providerId): array
    {
        return self::raw("SELECT pr.*, u.name AS patient_name FROM provider_reviews pr
                           JOIN patients p ON p.id = pr.patient_id
                           JOIN users u ON u.id = p.user_id
                           WHERE pr.provider_id = ? AND pr.is_published = 1
                           ORDER BY pr.created_at DESC", [$providerId]);
    }

    public static function summaryFor(int $providerId): array
    {
        $row = self::rawOne("SELECT COUNT(*) AS total, COALESCE(AVG(rating),0) AS average
                              FROM provider_reviews WHERE provider_id = ? AND is_published = 1", [$providerId]);
        return [
            'total' => (int) ($row['total'] ?? 0),
            'average' => round((float) ($row['average'] ?? 0), 1),
        ];
    }

    public static function summaryForMany(array $providerIds): array
    {
        if (empty($providerIds)) return [];
        $placeholders = implode(',', array_fill(0, count($providerIds), '?'));
        $rows = self::raw("SELECT provider_id, COUNT(*) AS total, COALESCE(AVG(rating),0) AS average
                            FROM provider_reviews WHERE provider_id IN ($placeholders) AND is_published = 1
                            GROUP BY provider_id", $providerIds);
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['provider_id']] = ['total' => (int) $r['total'], 'average' => round((float) $r['average'], 1)];
        }
        return $map;
    }

    public static function alreadyReviewed(int $consultationId): bool
    {
        return (bool) self::rawOne('SELECT id FROM provider_reviews WHERE consultation_id = ?', [$consultationId]);
    }
}
