<?php
namespace App\Models;
use Core\Model;

class ConcernKeyword extends Model
{
    protected static string $table = 'concern_keywords';

    public static function all_(): array
    {
        return self::raw('SELECT ck.*, ct.name AS tag_name FROM concern_keywords ck JOIN concern_tags ct ON ct.id = ck.tag_id ORDER BY ct.name ASC');
    }

    /** Cocokkan teks bebas ke tag berdasarkan substring keyword (rule-based, bukan NLP). */
    public static function matchTagsFromText(string $text): array
    {
        $lower = mb_strtolower($text);
        $matched = [];
        foreach (self::raw('SELECT * FROM concern_keywords') as $kw) {
            if (mb_strlen($kw['keyword']) > 0 && str_contains($lower, mb_strtolower($kw['keyword']))) {
                $matched[$kw['tag_id']] = true;
            }
        }
        return array_keys($matched);
    }
}
