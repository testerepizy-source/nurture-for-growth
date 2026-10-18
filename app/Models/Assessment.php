<?php
namespace App\Models;
use Core\Model;

class Assessment extends Model
{
    protected static string $table = 'assessments';

    public static function activeList(): array
    {
        return self::raw("SELECT a.*, c.name AS category_name FROM assessments a
                           JOIN assessment_categories c ON c.id = a.category_id
                           WHERE a.is_active = 1 ORDER BY a.id DESC");
    }

    public static function withCategory(int $id): ?array
    {
        return self::rawOne("SELECT a.*, c.name AS category_name FROM assessments a
                              JOIN assessment_categories c ON c.id = a.category_id WHERE a.id = ?", [$id]);
    }
}
