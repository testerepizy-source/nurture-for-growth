<?php
namespace App\Models;
use Core\Model;

class ConcernTag extends Model
{
    protected static string $table = 'concern_tags';

    public static function active(): array
    {
        return self::raw('SELECT * FROM concern_tags WHERE is_active = 1 ORDER BY name ASC');
    }

    public static function tagsForProvider(int $userId): array
    {
        return self::raw("SELECT ct.* FROM concern_tags ct
                           JOIN provider_concern_tags pct ON pct.tag_id = ct.id
                           WHERE pct.user_id = ? ORDER BY ct.name ASC", [$userId]);
    }

    public static function syncForProvider(int $userId, array $tagIds): void
    {
        self::rawExecute('DELETE FROM provider_concern_tags WHERE user_id = ?', [$userId]);
        foreach ($tagIds as $tagId) {
            self::rawExecute('INSERT INTO provider_concern_tags (user_id, tag_id) VALUES (?, ?)', [$userId, (int) $tagId]);
        }
    }
}
