<?php
// Debug: dump respons mentah Google Places API (New).
require __DIR__ . '/../core/autoload.php';
require __DIR__ . '/../core/helpers.php';
require __DIR__ . '/../config/config.php';

$key = trim((string) env('GOOGLE_PLACES_API_KEY', ''));
$placeId = trim((string) env('GOOGLE_PLACE_ID', ''));

$ch = curl_init('https://places.googleapis.com/v1/places/' . rawurlencode($placeId) . '?languageCode=id');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Goog-Api-Key: ' . $key,
        'X-Goog-FieldMask: rating,reviews,displayName',
    ],
]);
$body = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP {$status}\n";
echo substr((string) $body, 0, 2000) . "\n";
