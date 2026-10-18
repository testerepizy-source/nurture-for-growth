<?php
namespace App\Controllers;

use Core\Controller;
use App\Services\AssessmentService;

class AssessmentController extends Controller
{
    private AssessmentService $service;
    public function __construct() { $this->service = new AssessmentService(); }

    public function index(): void
    {
        $assessments = $this->service->listActive();
        $history = $this->service->history(current_patient_id());
        $this->view('assessment.categories', ['assessments' => $assessments, 'history' => $history]);
    }

    public function take(array $params): void
    {
        $assessment = $this->service->getForTaking((int) $params['id']);
        if (!$assessment) { http_response_code(404); require __DIR__ . '/../Views/errors/404.php'; return; }
        $this->view('assessment.take', ['assessment' => $assessment]);
    }

    public function submit(array $params): void
    {
        $this->requireCsrf();
        $assessmentId = (int) $params['id'];
        $answers = $_POST['answers'] ?? [];
        $result = $this->service->submit(current_patient_id(), $assessmentId, $answers);

        audit('create', 'assessment', 'Client menyelesaikan asesmen ID ' . $assessmentId);

        if (!empty($result['is_high_risk'])) {
            \App\Models\CrisisEvent::create([
                'patient_id'      => current_patient_id(),
                'source'          => 'assessment',
                'matched_keyword' => null,
                'context_snippet' => 'Hasil assessment "' . $result['assessment_title'] . '" skor ' . $result['total_score'],
                'is_flagged'      => 1,
                'provider_notified' => 0,
            ]);
            audit('flag_high_risk', 'assessment', 'Hasil asesmen menunjukkan risiko tinggi, result ID ' . $result['id']);
        }

        $this->redirect('/assessment/result/' . $result['id']);
    }

    public function result(array $params): void
    {
        $result = $this->service->detail((int) $params['id']);
        if (!$result) { http_response_code(404); require __DIR__ . '/../Views/errors/404.php'; return; }
        $isOwner = (int) $result['patient_id'] === current_patient_id();
        $isAssignedProvider = false;
        if (!$isOwner && \Core\Auth::can('assessment.view_patient')) {
            $patient = \App\Models\Patient::find((int) $result['patient_id']);
            $providerId = \Core\Auth::id();
            $isAssignedProvider = $patient && (
                (int) ($patient['assigned_psychologist_id'] ?? 0) === $providerId ||
                (int) ($patient['assigned_psychiatrist_id'] ?? 0) === $providerId
            );
        }
        if (!$isOwner && !$isAssignedProvider && \Core\Auth::roleName() !== 'super_admin') {
            http_response_code(403); require __DIR__ . '/../Views/errors/403.php'; return;
        }
        $this->view('assessment.result', ['result' => $result]);
    }

    public function history(): void
    {
        $history = $this->service->history(current_patient_id());
        $this->view('assessment.history', ['history' => $history]);
    }
}
