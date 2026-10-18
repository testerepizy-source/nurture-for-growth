<?php
namespace App\Models;
use Core\Model;

class Psychiatrist extends Model
{
    protected static string $table = 'psychiatrists';

    public static function allWithUser(): array
    {
        return self::raw("SELECT ps.*, u.name, u.email, u.phone, u.status, u.photo
                           FROM psychiatrists ps JOIN users u ON u.id = ps.user_id ORDER BY u.name ASC");
    }

    public static function byUserId(int $userId): ?array { return self::findBy('user_id', $userId); }
}
