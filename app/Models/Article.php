<?php
namespace App\Models;
use Core\Model;

class Article extends Model
{
    protected static string $table = 'articles';

    public static function published(): array
    {
        return self::raw("SELECT a.*, c.name AS category_name FROM articles a
                           LEFT JOIN article_categories c ON c.id = a.category_id
                           WHERE a.is_published = 1 ORDER BY a.created_at DESC");
    }

    public static function bySlug(string $slug): ?array
    {
        return self::rawOne("SELECT a.*, c.name AS category_name FROM articles a
                              LEFT JOIN article_categories c ON c.id = a.category_id
                              WHERE a.slug = ?", [$slug]);
    }

    public static function categoriesWithCount(): array
    {
        return self::raw("SELECT c.id, c.name, c.slug, COUNT(a.id) AS total
                           FROM article_categories c
                           JOIN articles a ON a.category_id = c.id AND a.is_published = 1
                           GROUP BY c.id ORDER BY c.name ASC");
    }

    public static function popularTags(int $limit = 8): array
    {
        $rows = self::raw("SELECT tag FROM articles WHERE is_published = 1 AND tag IS NOT NULL AND tag != ''");
        $counts = [];
        foreach ($rows as $r) {
            foreach (array_map('trim', explode(',', $r['tag'])) as $t) {
                if ($t === '') continue;
                $counts[$t] = ($counts[$t] ?? 0) + 1;
            }
        }
        arsort($counts);
        return array_slice(array_keys($counts), 0, $limit);
    }

    public static function recent(int $limit = 3, ?int $excludeId = null): array
    {
        $all = self::published();
        if ($excludeId) {
            $all = array_values(array_filter($all, fn($a) => (int) $a['id'] !== $excludeId));
        }
        return array_slice($all, 0, $limit);
    }

    public static function search(string $keyword): array
    {
        $like = '%' . $keyword . '%';
        return self::raw("SELECT a.*, c.name AS category_name FROM articles a
                           LEFT JOIN article_categories c ON c.id = a.category_id
                           WHERE a.is_published = 1 AND (a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)
                           ORDER BY a.created_at DESC", [$like, $like, $like]);
    }
}
