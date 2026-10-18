<?php
namespace App\Models;
use Core\Model;

class PaymentChannel extends Model
{
    protected static string $table = 'payment_channels';

    public const TYPES = ['bank', 'qris'];

    public static function allOrdered(): array
    {
        return self::raw("SELECT * FROM payment_channels ORDER BY type ASC, sort_order ASC, id ASC");
    }

    public static function active(): array
    {
        return self::raw("SELECT * FROM payment_channels WHERE is_active = 1 ORDER BY type ASC, sort_order ASC, id ASC");
    }

    /** @return array<int, array> channel bank yang aktif */
    public static function activeBanks(): array
    {
        return array_values(array_filter(self::active(), fn($c) => $c['type'] === 'bank'));
    }

    /** @return array<int, array> channel QRIS yang aktif */
    public static function activeQris(): array
    {
        return array_values(array_filter(self::active(), fn($c) => $c['type'] === 'qris'));
    }

    /** Ada minimal satu channel manual aktif? */
    public static function hasActive(): bool
    {
        return self::count('is_active = 1') > 0;
    }
}
