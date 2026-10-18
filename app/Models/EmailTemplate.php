<?php
namespace App\Models;
use Core\Model;

class EmailTemplate extends Model
{
    protected static string $table = 'email_templates';

    public static function byCode(string $code): ?array { return self::findBy('code', $code); }
}
