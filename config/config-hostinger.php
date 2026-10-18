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
    'app_url'    => env('APP_URL', 'https://nurtureforgrowth.com/'),
    'app_env'    => env('APP_ENV', 'local'),
    'app_debug'  => env('APP_DEBUG', '1') === '1',
    'timezone'   => env('APP_TIMEZONE', 'Asia/Jakarta'),

    'db' => [
        'host'    => env('DB_HOST', '127.0.0.1'),
        'port'    => env('DB_PORT', '3306'),
        'name'    => env('DB_NAME', 'u716873515_nfg'),
        'user'    => env('DB_USER', 'u716873515_nfg'),
        'pass'    => env('DB_PASS', '@1Infinity'),
        'charset' => 'utf8mb4',
    ],

    'mail' => [
        'mailer' => env('MAIL_MAILER', 'smtp'),
        'host' => env('MAIL_HOST', 'smtp.gmail.com'),
        'port' => env('MAIL_PORT', '587'),
        'user' => env('MAIL_USER', 'admin@nurtureforgrowth.com'),
        'pass' => env('MAIL_PASS', 'abrlchnsrpcijqvr'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from' => env('MAIL_FROM', 'admin@nurtureforgrowth.com'),
    ],

    'session_name' => 'nfg_session',
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
];
