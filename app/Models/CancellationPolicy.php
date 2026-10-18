<?php
namespace App\Models;
use Core\Model;

class CancellationPolicy extends Model
{
    protected static string $table = 'cancellation_policies';

    public const FULL_REFUND_HOURS = 24;
    public const FORFEIT_HOURS = 12;

    public static function active(): array
    {
        return [
            ['min_hours_before' => 24, 'refund_percentage' => 100, 'description' => 'Pembatalan setidaknya 24 jam sebelum jadwal: refund 100%.'],
            ['min_hours_before' => 12, 'refund_percentage' => 50, 'description' => 'Pembatalan 12 sampai kurang dari 24 jam sebelum jadwal: refund 50%.'],
            ['min_hours_before' => 0, 'refund_percentage' => 0, 'description' => 'Pembatalan kurang dari 12 jam sebelum jadwal: dana hangus (refund 0%).'],
        ];
    }

    /** Refund: >=24 jam 100%, 12 sampai kurang dari 24 jam 50%, kurang dari 12 jam 0%. */
    public static function refundPercentageFor(float $hoursBefore): int
    {
        if ($hoursBefore >= self::FULL_REFUND_HOURS) return 100;
        if ($hoursBefore >= self::FORFEIT_HOURS) return 50;
        return 0;
    }
}
