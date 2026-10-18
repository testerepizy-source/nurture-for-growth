<?php
/**
 * Sinkronisasi ulasan Google Maps -> tabel google_reviews.
 *
 * Syarat: isi GOOGLE_PLACES_API_KEY dan GOOGLE_PLACE_ID di file .env
 * (Place ID bisa dilihat di Google Maps Platform -> Places API -> Place ID Finder).
 *
 * Pakai:  php scripts/sync-google-reviews.php
 */

require __DIR__ . '/../core/autoload.php';
require __DIR__ . '/../core/helpers.php';
require __DIR__ . '/../config/config.php';

$ok = \App\Services\GoogleReviewService::sync();
if (!$ok) {
    echo "GAGAL mengambil ulasan dari Google Places API.\n";
    echo "Cek: (1) GOOGLE_PLACES_API_KEY & GOOGLE_PLACE_ID terisi di .env,\n";
    echo "     (2) Places API (New) sudah diaktifkan di Google Cloud Console,\n";
    echo "     (3) log error aplikasi.\n";
    exit(1);
}

$count = \App\Models\GoogleReview::count("source = 'google'");
echo "Sinkron OK: {$count} ulasan Google tersimpan di tabel google_reviews.\n";
