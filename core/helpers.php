<?php

use Core\Session;
use Core\Auth;
use App\Models\AuditLog;

require_once __DIR__ . '/../app/Views/partials/icons.php';

/** Returns the base sub-path the app is installed under (empty if at domain root). */
function base_path(): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (str_ends_with($scriptDir, '/public')) {
        $scriptDir = substr($scriptDir, 0, -7);
    }
    return rtrim($scriptDir, '/') ?: '';
}

/** Build a full URL relative to the app base path. */
function base_url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $assetPath = 'assets/' . ltrim($path, '/');
    $url = base_url($assetPath);
    $filePath = __DIR__ . '/../public/' . str_replace('/', DIRECTORY_SEPARATOR, $assetPath);

    if (file_exists($filePath)) {
        $url .= (str_contains($url, '?') ? '&' : '?') . 'v=' . filemtime($filePath);
    }

    return $url;
}

function absolute_url(string $path = ''): string
{
    $appUrl = rtrim(config_get('app_url', 'http://localhost'), '/');
    return $appUrl . '/' . ltrim($path, '/');
}

function request_absolute_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == '443' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    return $scheme . '://' . $host . base_url($path);
}

function absolute_asset(string $path): string
{
    return absolute_url('assets/' . ltrim($path, '/'));
}

function current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == '443' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $requestUri;
}

function canonical_url(): string
{
    $url = current_url();
    $parts = parse_url($url);
    if (!$parts) {
        return $url;
    }

    $scheme = $parts['scheme'] ?? 'http';
    $host = $parts['host'] ?? 'localhost';
    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    $path = $parts['path'] ?? '/';

    return $scheme . '://' . $host . $port . $path;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function old(string $key, $default = '')
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function config_get(string $key, $default = null)
{
    static $config = null;
    if ($config === null) $config = require __DIR__ . '/../config/config.php';

    if (strpos($key, '.') === false) {
        return $config[$key] ?? $default;
    }

    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function format_date(?string $date, string $format = 'd M Y'): string
{
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function format_datetime(?string $date, string $format = 'd M Y H:i'): string
{
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function audit(string $action, string $module, string $description = ''): void
{
    try {
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    } catch (\Throwable $e) {
        \Core\Logger::error('Audit log failed: ' . $e->getMessage());
    }
}

/** Resolve the patients.id row for the currently logged-in user (role: pasien). */
function current_patient_id(): ?int
{
    static $cached = null;
    if ($cached !== null) return $cached;
    $userId = Auth::id();
    if (!$userId) return null;
    $patient = null;
    if (Auth::roleName() === 'pasien') {
        $patient = App\Models\FamilyProfileAccess::ensurePrimaryProfile($userId);
        $activePatientId = (int) Session::get('active_patient_id', 0);
        if ($activePatientId && App\Models\FamilyProfileAccess::canAccess($userId, $activePatientId)) {
            $patient = App\Models\Patient::find($activePatientId);
        }
    } else {
        $patient = App\Models\Patient::byUserId($userId);
    }
    $cached = $patient['id'] ?? null;
    return $cached;
}

/** Very small crisis-keyword detector used by Journal / AI Assistant modules. */
function detect_crisis(string $text): ?string
{
    $keywords = App\Models\CrisisKeyword::all();
    $lower = mb_strtolower($text);
    foreach ($keywords as $k) {
        if (str_contains($lower, mb_strtolower($k['keyword']))) {
            return $k['keyword'];
        }
    }
    return null;
}

/** Translation helper - get translated string */
function trans(string $key, array $replace = []): string
{
    return \Core\Localization::get($key, $replace);
}

/** Short alias for trans() */
function __(string $key, array $replace = []): string
{
    return trans($key, $replace);
}

/** Get current locale */
function locale(): string
{
    return \Core\Localization::getCurrentLocale();
}

/** Set current locale */
function set_locale(string $locale): void
{
    \Core\Localization::setLocale($locale);
}
