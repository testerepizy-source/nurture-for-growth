<?php
namespace App\Models;
use Core\Model;

class NotificationTemplate extends Model
{
    protected static string $table = 'notification_templates';

    public static function byCode(string $code): ?array { return self::findBy('code', $code); }

    public static function render(string $code, array $vars): ?string
    {
        $tpl = self::byCode($code);
        if (!$tpl) return null;
        $body = $tpl['body'];
        foreach ($vars as $k => $v) { $body = str_replace('{' . $k . '}', $v, $body); }
        return $body;
    }
}
