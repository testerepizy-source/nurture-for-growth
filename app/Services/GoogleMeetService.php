<?php
namespace App\Services;

use App\Models\IntegrationConfig;
use Core\Logger;

class GoogleMeetService
{
    public static function createMeeting(array $consultation, array $patient, array $provider): ?string
    {
        $integration = IntegrationConfig::byProvider('google');
        $config = json_decode($integration['config_json'] ?? '{}', true) ?: [];
        $envConfig = [
            'client_id' => config_get('google.client_id', ''),
            'client_secret' => config_get('google.client_secret', ''),
            'refresh_token' => config_get('google.refresh_token', ''),
            'calendar_id' => config_get('google.calendar_id', 'primary'),
        ];
        $config = array_merge($config, array_filter($envConfig, fn($value) => $value !== ''));

        if (!$integration || empty($integration['is_active'])) {
            Logger::warning('Google Meet: integration_configs/google tidak aktif atau tidak ditemukan, menggunakan .env jika tersedia.');
        }
        if (empty($config['client_id']) || empty($config['client_secret']) || empty($config['refresh_token'])) {
            Logger::warning('Google Meet tidak dibuat: konfigurasi Google Calendar API belum lengkap.');
            return null;
        }

        $accessToken = self::accessToken($config);
        if (!$accessToken) return null;

        $start = date(DATE_RFC3339, strtotime($consultation['schedule_at']));
        $end = date(DATE_RFC3339, strtotime($consultation['schedule_at']) + ((int) $consultation['duration_minutes'] * 60));
        $event = [
            'summary' => 'Konseling NFG: ' . ($patient['name'] ?? 'Pasien') . ' & ' . ($provider['name'] ?? 'Psikolog'),
            'description' => 'Sesi konseling Nurture For Growth (NFG).',
            'start' => ['dateTime' => $start, 'timeZone' => config_get('timezone', 'Asia/Jakarta')],
            'end' => ['dateTime' => $end, 'timeZone' => config_get('timezone', 'Asia/Jakarta')],
            'attendees' => array_values(array_filter([
                !empty($patient['email']) ? ['email' => $patient['email']] : null,
                !empty($provider['email']) ? ['email' => $provider['email']] : null,
            ])),
            'conferenceData' => [
                'createRequest' => ['requestId' => 'nfg-' . $consultation['id'] . '-' . bin2hex(random_bytes(6))],
            ],
        ];
        $calendarId = rawurlencode($config['calendar_id'] ?? 'primary');
        $url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendarId
            . '/events?conferenceDataVersion=1&sendUpdates=all';
        $response = self::request($url, 'POST', json_encode($event), ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
        $data = $response ? json_decode($response, true) : null;
        $link = $data['hangoutLink'] ?? $data['conferenceData']['entryPoints'][0]['uri'] ?? null;
        if (!$link) Logger::error('Google Calendar event dibuat tanpa link Meet atau API menolak permintaan.');
        return $link;
    }

    private static function accessToken(array $config): ?string
    {
        $body = http_build_query([
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $config['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);
        $response = self::request('https://oauth2.googleapis.com/token', 'POST', $body, ['Content-Type: application/x-www-form-urlencoded']);
        $data = $response ? json_decode($response, true) : null;
        if (empty($data['access_token'])) {
            Logger::error('Gagal memperoleh access token Google Calendar.');
            return null;
        }
        return $data['access_token'];
    }

    private static function request(string $url, string $method, string $body, array $headers): string|false
    {
        if (!function_exists('curl_init')) {
            Logger::error('Ekstensi cURL diperlukan untuk integrasi Google Meet.');
            return false;
        }
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($response === false || $status < 200 || $status >= 300) {
            Logger::error('Google Calendar API request failed with status ' . $status . '.');
            return false;
        }
        return $response;
    }
}
