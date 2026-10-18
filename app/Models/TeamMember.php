<?php
namespace App\Models;
use Core\Model;

class TeamMember extends Model
{
    protected static string $table = 'team_members';

    public static function active(): array
    {
        return self::raw('SELECT * FROM team_members WHERE is_active = 1 ORDER BY order_no ASC, id ASC');
    }
}
