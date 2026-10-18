<?php

namespace Core;

use App\Models\User;

class Localization
{
    private static ?string $currentLocale = null;
    private static array $translations = [];
    private static array $supportedLocales = ['id', 'en'];
    private static string $defaultLocale = 'id';

    public static function initialize(): void
    {
        self::$currentLocale = self::detectLocale();
        self::loadTranslations(self::$currentLocale);
    }

    private static function detectLocale(): string
    {
        if (Auth::check()) {
            $user = User::find(Auth::id());
            if ($user && !empty($user['language_preference'])) {
                return in_array($user['language_preference'], self::$supportedLocales)
                    ? $user['language_preference']
                    : self::$defaultLocale;
            }
        }

        if (isset($_COOKIE['nfg_locale']) && in_array($_COOKIE['nfg_locale'], self::$supportedLocales)) {
            return $_COOKIE['nfg_locale'];
        }

        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (str_contains($acceptLanguage, 'id')) {
            return 'id';
        }
        if (str_contains($acceptLanguage, 'en')) {
            return 'en';
        }

        return self::$defaultLocale;
    }

    private static function loadTranslations(string $locale): void
    {
        $file = __DIR__ . '/../resources/lang/' . $locale . '.php';
        if (file_exists($file)) {
            self::$translations = require $file;
        }
    }

    public static function get(string $key, array $replace = []): string
    {
        $value = self::$translations[$key] ?? $key;

        if (!empty($replace)) {
            foreach ($replace as $search => $replaceValue) {
                $value = str_replace(':' . $search, $replaceValue, $value);
            }
        }

        return $value;
    }

    public static function getCurrentLocale(): string
    {
        return self::$currentLocale ?? self::$defaultLocale;
    }

    public static function setLocale(string $locale): void
    {
        if (in_array($locale, self::$supportedLocales)) {
            self::$currentLocale = $locale;
            self::loadTranslations($locale);
            setcookie('nfg_locale', $locale, time() + (365 * 24 * 60 * 60), '/', '', false, true);

            if (Auth::check()) {
                User::update(Auth::id(), ['language_preference' => $locale]);
            }
        }
    }

    public static function getSupportedLocales(): array
    {
        return self::$supportedLocales;
    }

    public static function getLocaleLabel(string $locale): string
    {
        return match ($locale) {
            'id' => 'Bahasa Indonesia',
            'en' => 'English',
            default => $locale,
        };
    }
}
