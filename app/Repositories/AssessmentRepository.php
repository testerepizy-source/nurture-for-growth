<?php
namespace App\Repositories;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\AssessmentScoreRange;
use App\Models\AssessmentResult;
use App\Models\AssessmentAnswer;

class AssessmentRepository
{
    public function activeList(): array { return Assessment::activeList(); }

    public function detailWithQuestions(int $assessmentId): ?array
    {
        $assessment = Assessment::withCategory($assessmentId);
        if (!$assessment) return null;
        $questions = Question::forAssessment($assessmentId);
        foreach ($questions as &$q) {
            $q['options'] = QuestionOption::forQuestion($q['id']);
        }
        $assessment['questions'] = $questions;
        return $assessment;
    }

    public function saveResult(int $patientId, int $assessmentId, array $answers): int
    {
        $totalScore = 0;
        $optionIds = [];
        foreach ($answers as $questionId => $optionId) {
            $option = QuestionOption::find((int) $optionId);
            if ($option) {
                $totalScore += (int) $option['score'];
                $optionIds[(int) $questionId] = (int) $optionId;
            }
        }

        $range = AssessmentScoreRange::matchScore($assessmentId, $totalScore);

        $resultId = AssessmentResult::create([
            'patient_id'    => $patientId,
            'assessment_id' => $assessmentId,
            'total_score'   => $totalScore,
            'score_range_id'=> $range['id'] ?? null,
            'status'        => 'completed',
            'started_at'    => date('Y-m-d H:i:s'),
            'completed_at'  => date('Y-m-d H:i:s'),
        ]);

        foreach ($optionIds as $questionId => $optionId) {
            AssessmentAnswer::create([
                'result_id'   => $resultId,
                'question_id' => $questionId,
                'option_id'   => $optionId,
            ]);
        }

        return $resultId;
    }

    public function historyForPatient(int $patientId): array { return AssessmentResult::historyForPatient($patientId); }
    public function resultDetail(int $id): ?array { return AssessmentResult::detail($id); }
}
