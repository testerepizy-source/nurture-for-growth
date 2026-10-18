<?php
namespace App\Models;
use Core\Model;

class Question extends Model
{
    protected static string $table = 'questions';

    public static function forAssessment(int $assessmentId): array
    {
        return self::raw('SELECT * FROM questions WHERE assessment_id = ? ORDER BY order_no ASC, id ASC', [$assessmentId]);
    }
}
