<?php
namespace App\Models;
use Core\Model;

class AssessmentScoreRange extends Model
{
    protected static string $table = 'assessment_score_ranges';

    public static function forAssessment(int $assessmentId): array
    {
        return self::raw('SELECT * FROM assessment_score_ranges WHERE assessment_id = ? ORDER BY min_score ASC', [$assessmentId]);
    }

    public static function matchScore(int $assessmentId, int $score): ?array
    {
        return self::rawOne('SELECT * FROM assessment_score_ranges WHERE assessment_id = ? AND ? BETWEEN min_score AND max_score LIMIT 1', [$assessmentId, $score]);
    }
}
