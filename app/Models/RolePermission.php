<?php
namespace App\Models;
use Core\Model;

class RolePermission extends Model
{
    protected static string $table = 'role_permission';

    public static function forRole(int $roleId): array
    {
        return self::raw('SELECT permission_id FROM role_permission WHERE role_id = ?', [$roleId]);
    }

    public static function sync(int $roleId, array $permissionIds): void
    {
        self::rawExecute('DELETE FROM role_permission WHERE role_id = ?', [$roleId]);
        foreach ($permissionIds as $pid) {
            self::create(['role_id' => $roleId, 'permission_id' => (int) $pid]);
        }
    }
}
