<?php
namespace App\Models;
use Core\Model;

class AssessmentResult extends Model
{
    protected static string $table = 'assessment_results';

    public static function historyForPatient(int $patientId): array
    {
        return self::raw("SELECT ar.*, a.title AS assessment_title, sr.category_label, sr.is_high_risk
                           FROM assessment_results ar
                           JOIN assessments a ON a.id = ar.assessment_id
                           LEFT JOIN assessment_score_ranges sr ON sr.id = ar.score_range_id
                           WHERE ar.patient_id = ? ORDER BY ar.created_at DESC", [$patientId]);
    }

    public static function detail(int $id): ?array
    {
        return self::rawOne("SELECT ar.*, a.title AS assessment_title, sr.category_label, sr.interpretation,
                              sr.suggestion, sr.is_high_risk, u.name AS patient_name, ar.patient_id
                              FROM assessment_results ar
                              JOIN assessments a ON a.id = ar.assessment_id
                              JOIN patients p ON p.id = ar.patient_id
                              JOIN users u ON u.id = p.user_id
                              LEFT JOIN assessment_score_ranges sr ON sr.id = ar.score_range_id
                              WHERE ar.id = ?", [$id]);
    }
}
