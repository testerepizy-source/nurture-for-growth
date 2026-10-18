<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use Core\Middleware;
use App\Models\Patient;
use App\Models\AssessmentResult;
use App\Models\Mood;
use App\Models\Journal;
use App\Models\JournalAccessGrant;
use App\Models\MedicalNote;
use App\Models\TherapyHistory;
use App\Models\ControlPlan;
use App\Models\Consultation;
use App\Models\InformedConsent;

class PatientController extends Controller
{
    public function index(): void
    {
        Middleware::permission('patient.view_assigned');
        $role = Auth::roleName();
        $patients = Patient::assignedTo(Auth::id(), $role);
        $this->view('patient.index', ['patients' => $patients]);
    }

    public function show(array $params): void
    {
        Middleware::permission('patient.view_assigned');
        $patientId = (int) $params['id'];
        $patient = Patient::rawOne("SELECT p.*, u.name, u.email, u.phone, u.gender, u.birth_date, u.address, u.photo
                       FROM patients p JOIN users u ON u.id = p.user_id WHERE p.id = ?", [$patientId]);
        if (!$patient) { http_response_code(404); require __DIR__.'/../Views/errors/404.php'; return; }
        if (!$this->isAssigned($patientId)) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }

        $assessments = AssessmentResult::raw("SELECT ar.*, a.title AS assessment_title, sr.category_label, sr.interpretation, sr.suggestion, sr.is_high_risk
                              FROM assessment_results ar
                              JOIN assessments a ON a.id = ar.assessment_id
                              LEFT JOIN assessment_score_ranges sr ON sr.id = ar.score_range_id
                              WHERE ar.patient_id = ? ORDER BY ar.created_at DESC", [$patientId]);
        $moods = Mood::forPatient($patientId, 60);

        $accessibleJournalIds = array_column(
            \App\Models\JournalAccessGrant::raw('SELECT journal_id FROM journal_access_grants WHERE granted_to_user_id = ?', [Auth::id()]),
            'journal_id'
        );
        $journals = array_filter(Journal::forPatient($patientId), fn($j) => in_array($j['id'], $accessibleJournalIds, true));

        Consultation::syncExpiredForPatientProvider($patientId, Auth::id());

        $consultations = Consultation::raw("SELECT c.*, u.name AS provider_name
                            FROM consultations c
                            JOIN users u ON u.id = c.provider_id
                                                        WHERE c.patient_id = ?
                                                            AND c.provider_id = ?
                                                            AND (c.status IN ('completed', 'no_show') OR DATE_ADD(c.schedule_at, INTERVAL c.duration_minutes MINUTE) >= NOW())
                            ORDER BY c.schedule_at DESC", [$patientId, Auth::id()]);

        $medicalNotes = Auth::roleName() === 'psikiater' ? MedicalNote::forPatient($patientId) : [];
        $therapyHistory = Auth::roleName() === 'psikiater' ? TherapyHistory::forPatient($patientId) : [];
        $controlPlans = Auth::roleName() === 'psikiater' ? ControlPlan::forPatient($patientId) : [];
        $prescriptions = Auth::roleName() === 'psikiater' ? \App\Models\Prescription::forPatient($patientId) : [];
        $informedConsent = InformedConsent::forPatient($patientId);

        $this->view('patient.show', compact('patient', 'assessments', 'moods', 'journals', 'consultations', 'medicalNotes', 'therapyHistory', 'controlPlans', 'prescriptions', 'informedConsent'));
    }

    public function addMedicalNote(array $params): void
    {
        Middleware::permission('medical_note.manage');
        $this->requireCsrf();
        $patientId = (int) $params['id'];
        if (!$this->isAssigned($patientId)) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }
        MedicalNote::create([
            'patient_id' => $patientId,
            'psychiatrist_id' => Auth::id(),
            'diagnosis_note' => $this->input('diagnosis_note'),
            'medication_note' => $this->input('medication_note'),
        ]);
        audit('create', 'medical_note', 'Menambahkan catatan medis untuk client ID ' . $patientId);
        Session::flash('success', 'Catatan medis berhasil disimpan.');
        $this->redirect('/patients/' . $patientId);
    }

    public function addTherapyHistory(array $params): void
    {
        Middleware::permission('medical_note.manage');
        $this->requireCsrf();
        $patientId = (int) $params['id'];
        if (!$this->isAssigned($patientId)) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }
        TherapyHistory::create([
            'patient_id' => $patientId,
            'therapy_type' => $this->input('therapy_type'),
            'description' => $this->input('description'),
            'started_at' => $this->input('started_at') ?: null,
            'ended_at' => $this->input('ended_at') ?: null,
            'outcome_note' => $this->input('outcome_note'),
            'recorded_by' => Auth::id(),
        ]);
        audit('create', 'therapy_history', 'Menambahkan riwayat terapi untuk client ID ' . $patientId);
        Session::flash('success', 'Riwayat terapi berhasil disimpan.');
        $this->redirect('/patients/' . $patientId);
    }

    public function addControlPlan(array $params): void
    {
        Middleware::permission('medical_note.manage');
        $this->requireCsrf();
        $patientId = (int) $params['id'];
        if (!$this->isAssigned($patientId)) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }
        ControlPlan::create([
            'patient_id' => $patientId,
            'psychiatrist_id' => Auth::id(),
            'next_control_date' => $this->input('next_control_date'),
            'note' => $this->input('note'),
            'status' => 'scheduled',
        ]);
        audit('create', 'control_plan', 'Menambahkan rencana kontrol untuk client ID ' . $patientId);
        Session::flash('success', 'Rencana kontrol berhasil disimpan.');
        $this->redirect('/patients/' . $patientId);
    }

    private function isAssigned(int $patientId): bool
    {
        $patient = Patient::find($patientId);
        if (!$patient) return false;

        if ((int) ($patient['assigned_psychologist_id'] ?? 0) === Auth::id()
            || (int) ($patient['assigned_psychiatrist_id'] ?? 0) === Auth::id()
            || Auth::roleName() === 'super_admin') {
            return true;
        }

        return Consultation::count(
            'patient_id = ? AND provider_id = ? AND status IN (\'pending\', \'approved\', \'completed\', \'no_show\')',
            [$patientId, Auth::id()]
        ) > 0;
    }
}
