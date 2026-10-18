<?php
namespace App\Models;
use Core\Model;

class Setting extends Model
{
    protected static string $table = 'settings';

    public static function get(string $key, $default = null)
    {
        $row = self::rawOne('SELECT `value` FROM settings WHERE `key` = ?', [$key]);
        return $row ? $row['value'] : $default;
    }

    public static function set(string $key, string $value): void
    {
        $existing = self::rawOne('SELECT id FROM settings WHERE `key` = ?', [$key]);
        if ($existing) {
            self::rawExecute('UPDATE settings SET `value` = ? WHERE `key` = ?', [$value, $key]);
        } else {
            self::create(['key' => $key, 'value' => $value]);
        }
    }
}
