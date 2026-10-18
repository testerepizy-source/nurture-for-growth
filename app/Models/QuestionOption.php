<?php
namespace App\Models;
use Core\Model;

class QuestionOption extends Model
{
    protected static string $table = 'question_options';

    public static function forQuestion(int $questionId): array
    {
        return self::raw('SELECT * FROM question_options WHERE question_id = ? ORDER BY order_no ASC, id ASC', [$questionId]);
    }
}
