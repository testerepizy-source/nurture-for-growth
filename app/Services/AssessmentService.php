<?php
namespace App\Services;

use App\Repositories\AssessmentRepository;

/**
 * Business rules for scoring & interpreting assessments.
 * Kept separate from the repository (data access) per Service Layer pattern.
 */
class AssessmentService
{
    private AssessmentRepository $repo;

    public function __construct() { $this->repo = new AssessmentRepository(); }

    public function listActive(): array { return $this->repo->activeList(); }

    public function getForTaking(int $assessmentId): ?array { return $this->repo->detailWithQuestions($assessmentId); }

    public function submit(int $patientId, int $assessmentId, array $answers): array
    {
        $resultId = $this->repo->saveResult($patientId, $assessmentId, $answers);
        return $this->repo->resultDetail($resultId);
    }

    public function history(int $patientId): array { return $this->repo->historyForPatient($patientId); }

    public function detail(int $resultId): ?array { return $this->repo->resultDetail($resultId); }
}
