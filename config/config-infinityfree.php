<?php
/**
 * Global application configuration.
 * Copy .env.example to .env and adjust, or edit directly for a quick start.
 */

if (!function_exists('env')) {
    $GLOBALS['__nfg_env'] = [];
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
            $GLOBALS['__nfg_env'][trim($k)] = trim($v);
        }
    }
    function env(string $key, $default = null) {
        return $GLOBALS['__nfg_env'][$key] ?? $default;
    }
}

return [
    'app_name'   => env('APP_NAME', 'NurtureForGrowth (NFG)'),
    'app_url'    => env('APP_URL', 'https://psyio.totalh.net'),
    'app_env'    => env('APP_ENV', 'local'),
    'app_debug'  => env('APP_DEBUG', '1') === '1',
    'timezone'   => env('APP_TIMEZONE', 'Asia/Jakarta'),

    'db' => [
        'host'    => env('DB_HOST', 'sql309.infinityfree.com'),
        'port'    => env('DB_PORT', '3306'),
        'name'    => env('DB_NAME', 'if0_37835763_nfg'),
        'user'    => env('DB_USER', 'if0_37835763'),
        'pass'    => env('DB_PASS', 'TVWGd4dEnH8'),
        'charset' => 'utf8mb4',
    ],

    'mail' => [
        'mailer' => env('MAIL_MAILER', 'smtp'),
        'host' => env('MAIL_HOST', 'smtp.gmail.com'),
        'port' => env('MAIL_PORT', '587'),
        'user' => env('MAIL_USERNAME', 'admin@nurtureforgrowth.com'),
        'pass' => env('MAIL_PASSWORD', 'abrlchnsrpcijqvr'),
        'from' => env('MAIL_FROM_ADDRESS', 'admin@nurtureforgrowth.com'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_name' => env('MAIL_FROM_NAME', 'NurtureForGrowth (NFG)'),
    ],

    'session_name' => 'nfg_session',
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
        'refresh_token' => env('GOOGLE_REFRESH_TOKEN', ''),
        'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
    ],
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY', ''),
        'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', '0') === '1',
    ],
];
