<?php
namespace App\Services;

/**
 * Kalkulasi tarif konsultasi berdasarkan Rate Card resmi NFG:
 * tergantung jenjang psikolog (Associate/Senior), jenis sesi
 * (individu/pasangan/keluarga), dan metode (online/tatap muka).
 */
class PriceCalculatorService
{
    private const RATE_CARD = [
        'associate' => [
            'individual' => ['online' => 375000, 'tatap_muka' => 475000],
            'couple'     => ['online' => 650000, 'tatap_muka' => 750000],
            'family'     => ['online' => 650000, 'tatap_muka' => 750000],
        ],
        'senior' => [
            'individual' => ['online' => 500000, 'tatap_muka' => 600000],
            'couple'     => ['online' => 850000, 'tatap_muka' => 950000],
            'family'     => ['online' => 850000, 'tatap_muka' => 950000],
        ],
    ];

    public static function calculate(string $tier, string $sessionType, string $method): int
    {
        $tier = in_array($tier, ['associate', 'senior'], true) ? $tier : 'associate';
        $sessionType = in_array($sessionType, ['individual', 'couple', 'family'], true) ? $sessionType : 'individual';
        $onlineOrOffline = $method === 'tatap_muka' ? 'tatap_muka' : 'online';

        return self::RATE_CARD[$tier][$sessionType][$onlineOrOffline] ?? 0;
    }

    public static function formatRupiah(int $amount): string
    {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }

    public static function tableFor(string $tier): array
    {
        return self::RATE_CARD[$tier] ?? self::RATE_CARD['associate'];
    }
}
