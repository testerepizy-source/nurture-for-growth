<?php

namespace App\Services;

use App\Models\GoogleReview;

/**
 * Sumber data ulasan Google Maps untuk section "Ulasan Client" landing.
 *
 * Alur: isi GOOGLE_PLACES_API_KEY + GOOGLE_PLACE_ID di .env, lalu layanan ini
 * menarik data live dari Google Places API (New): rating, jumlah ulasan, dan
 * (bila dikembalikan Google) teks ulasan. Hasilnya disimpan ke tabel
 * `google_reviews` dan agregatnya (rating/jumlah ulasan) ke tabel `settings`.
 * Sinkron ulang otomatis paling cepat 1x per 24 jam; untuk sinkron manual
 * jalankan scripts/sync-google-reviews.php.
 *
 * CATATAN KEBIJAKAN GOOGLE: teks ulasan di Places API (New) hanya
 * dikembalikan untuk partner ber-entitlement. Bila respons tidak memuat
 * reviews, layanan otomatis mencoba endpoint Places API LAMA (legacy) yang
 * masih mengembalikan teks ulasan -- aktifkan "Places API" (legacy) di
 * Google Cloud Console agar jalur ini aktif. Tanpa keduanya, section tetap
 * menampilkan ulasan yang tersimpan di tabel (transkrip manual/sinkron
 * sebelumnya) dan agregat rating tetap diperbarui.
 */
class GoogleReviewService
{
    private const TTL = 86400; // sinkron ulang otomatis paling cepat 24 jam sekali
    private const SETTING_SYNC = 'google_reviews_last_sync';
    private const SETTING_RATING = 'google_reviews_rating';
    private const SETTING_COUNT = 'google_reviews_review_count';

    /** Ulasan tampilan landing. Tidak pernah melempar error. */
    public static function latest(int $limit = 13): array
    {
        try {
            self::maybeSync();
        } catch (\Throwable $e) {
            \Core\Logger::error('GoogleReviewService::maybeSync gagal: ' . $e->getMessage());
        }
        try {
            return GoogleReview::active($limit);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Agregat tempat dari Google: ['rating' => ?float, 'review_count' => ?int, 'last_sync' => ?string]. */
    public static function placeInfo(): array
    {
        $out = ['rating' => null, 'review_count' => null, 'last_sync' => null];
        try {
            $stmt = \Core\Database::connection()->query(
                "SELECT `key`, `value` FROM settings WHERE `key` IN ('" .
                self::SETTING_SYNC . "','" . self::SETTING_RATING . "','" . self::SETTING_COUNT . "')"
            );
            foreach ($stmt->fetchAll() as $row) {
                if ($row['key'] === self::SETTING_RATING) {
                    $out['rating'] = (float) $row['value'];
                } elseif ($row['key'] === self::SETTING_COUNT) {
                    $out['review_count'] = (int) $row['value'];
                } elseif ($row['key'] === self::SETTING_SYNC) {
                    $out['last_sync'] = $row['value'];
                }
            }
        } catch (\Throwable $e) {
            // abaikan - agregat hanya pelengkap
        }
        return $out;
    }

    /** Sinkron otomatis bila API key terkonfigurasi & penanda terakhir sinkron sudah lebih dari TTL. */
    public static function maybeSync(): void
    {
        if (!function_exists('env')) return;
        if (trim((string) env('GOOGLE_PLACES_API_KEY', '')) === '' || trim((string) env('GOOGLE_PLACE_ID', '')) === '') {
            return;
        }
        $info = self::placeInfo();
        if (!empty($info['last_sync']) && strtotime($info['last_sync']) > time() - self::TTL) {
            return;
        }
        self::sync();
    }

    /** Tarik data Google & perbarui tabel google_reviews + agregat di settings. True bila request Google berhasil. */
    public static function sync(): bool
    {
        $data = self::fetchFromGoogle();
        if ($data === null) return false;

        $db = \Core\Database::connection();
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM google_reviews WHERE source = 'google'")->execute();
            $ins = $db->prepare(
                'INSERT INTO google_reviews (author_name, author_photo, rating, relative_time, review_text, review_time, source, is_active, sort_order)' .
                " VALUES (?, ?, ?, ?, ?, ?, 'google', 1, ?)"
            );
            foreach (array_slice(array_values($data['reviews']), 0, 10) as $i => $r) {
                $ins->execute([
                    $r['author_name'], $r['author_photo'], $r['rating'],
                    $r['relative_time'], $r['review_text'], $r['review_time'], $i + 1,
                ]);
            }
            // Baris manual/lama yang isinya sama dengan hasil API tidak diduplikasi.
            $db->exec(
                "DELETE m FROM google_reviews m INNER JOIN google_reviews g" .
                " ON g.source = 'google' AND m.source <> 'google'" .
                " AND g.author_name = m.author_name AND g.review_text = m.review_text"
            );
            self::saveSetting($db, self::SETTING_SYNC, date('Y-m-d H:i:s'));
            if ($data['rating'] !== null) {
                self::saveSetting($db, self::SETTING_RATING, (string) $data['rating']);
            }
            if ($data['review_count'] !== null) {
                self::saveSetting($db, self::SETTING_COUNT, (string) $data['review_count']);
            }
            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            \Core\Logger::error('GoogleReviewService::sync gagal: ' . $e->getMessage());
            return false;
        }
    }

    private static function saveSetting(\PDO $db, string $key, string $value): void
    {
        $stmt = $db->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)' .
            ' ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute([$key, $value]);
    }

    /**
     * Tarik data dari Google: Places API (New) dulu; bila teks ulasan tidak
     * dikembalikan, coba Places API legacy. Null bila keduanya gagal.
     * Return: ['rating' => ?float, 'review_count' => ?int, 'reviews' => [...]]
     */
    public static function fetchFromGoogle(): ?array
    {
        $new = self::fetchFromNewApi();
        if ($new !== null && !empty($new['reviews'])) return $new;
        $legacy = self::fetchFromLegacyApi();
        if ($legacy !== null && !empty($legacy['reviews'])) return $legacy;
        return $new ?? $legacy;
    }

    /** Google Places API (New): place details dengan field mask header. */
    private static function fetchFromNewApi(): ?array
    {
        if (!function_exists('env') || !function_exists('curl_init')) return null;
        $key = trim((string) env('GOOGLE_PLACES_API_KEY', ''));
        $placeId = trim((string) env('GOOGLE_PLACE_ID', ''));
        if ($key === '' || $placeId === '') return null;

        $ch = curl_init('https://places.googleapis.com/v1/places/' . rawurlencode($placeId) . '?languageCode=id');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Goog-Api-Key: ' . $key,
                'X-Goog-FieldMask: rating,userRatingCount,reviews,displayName',
            ],
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $status !== 200) {
            \Core\Logger::error("GooglePlaces API (New) HTTP {$status}: " . substr((string) $body, 0, 300));
            return null;
        }

        $data = json_decode((string) $body, true) ?: [];
        $out = [
            'rating' => isset($data['rating']) ? (float) $data['rating'] : null,
            'review_count' => isset($data['userRatingCount']) ? (int) $data['userRatingCount'] : null,
            'reviews' => [],
        ];
        foreach (array_slice($data['reviews'] ?? [], 0, 10) as $r) {
            $text = trim((string) ($r['text']['text'] ?? ''));
            if ($text === '') continue;
            $out['reviews'][] = [
                'author_name'   => $r['authorAttribution']['displayName'] ?? 'Klien',
                'author_photo'  => $r['authorAttribution']['photoUri'] ?? null,
                'rating'        => max(1, min(5, (int) ($r['rating'] ?? 5))),
                'relative_time' => $r['relativePublishTimeDescription'] ?? null,
                'review_text'   => $text,
                'review_time'   => !empty($r['publishTime']) ? date('Y-m-d H:i:s', strtotime($r['publishTime'])) : null,
            ];
        }
        return $out;
    }

    /** Fallback Places API legacy (maps.googleapis.com) - masih mengembalikan teks ulasan bila API-nya diaktifkan. */
    private static function fetchFromLegacyApi(): ?array
    {
        if (!function_exists('env') || !function_exists('curl_init')) return null;
        $key = trim((string) env('GOOGLE_PLACES_API_KEY', ''));
        $placeId = trim((string) env('GOOGLE_PLACE_ID', ''));
        if ($key === '' || $placeId === '') return null;

        $url = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . rawurlencode($placeId)
             . '&language=id&fields=rating,user_ratings_total,reviews&key=' . rawurlencode($key);
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $status !== 200) return null;

        $data = json_decode((string) $body, true) ?: [];
        if (($data['status'] ?? '') !== 'OK') {
            \Core\Logger::error('GooglePlaces API (legacy) status ' . ($data['status'] ?? '?') . ': ' . ($data['error_message'] ?? ''));
            return null;
        }

        $res = $data['result'] ?? [];
        $out = [
            'rating' => isset($res['rating']) ? (float) $res['rating'] : null,
            'review_count' => isset($res['user_ratings_total']) ? (int) $res['user_ratings_total'] : null,
            'reviews' => [],
        ];
        foreach (array_slice($res['reviews'] ?? [], 0, 10) as $r) {
            $text = trim((string) ($r['text'] ?? ''));
            if ($text === '') continue;
            $out['reviews'][] = [
                'author_name'   => $r['author_name'] ?? 'Klien',
                'author_photo'  => $r['profile_photo_url'] ?? null,
                'rating'        => max(1, min(5, (int) ($r['rating'] ?? 5))),
                'relative_time' => $r['relative_time_description'] ?? null,
                'review_text'   => $text,
                'review_time'   => !empty($r['time']) ? date('Y-m-d H:i:s', (int) $r['time']) : null,
            ];
        }
        return $out;
    }
}
