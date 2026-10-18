<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use Core\Middleware;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Patient;

class PrescriptionController extends Controller
{
    public function create(array $params): void
    {
        Middleware::permission('medical_note.manage');
        $patientId = (int) $params['id'];
        $patient = Patient::detailWithUser($patientId);
        if (!$patient) { http_response_code(404); require __DIR__ . '/../Views/errors/404.php'; return; }
        $this->view('prescription.create', compact('patient'));
    }

    public function store(array $params): void
    {
        Middleware::permission('medical_note.manage');
        $this->requireCsrf();
        $patientId = (int) $params['id'];

        $prescriptionId = Prescription::create([
            'prescription_number' => Prescription::generateNumber(),
            'patient_id' => $patientId,
            'psychiatrist_id' => Auth::id(),
            'diagnosis_note' => $this->input('diagnosis_note'),
        ]);

        $names = $_POST['medication_name'] ?? [];
        $dosages = $_POST['dosage'] ?? [];
        $frequencies = $_POST['frequency'] ?? [];
        $durations = $_POST['duration'] ?? [];
        $instructions = $_POST['instructions'] ?? [];

        foreach ($names as $i => $name) {
            if (trim($name) === '') continue;
            PrescriptionItem::create([
                'prescription_id' => $prescriptionId,
                'medication_name' => $name,
                'dosage' => $dosages[$i] ?? null,
                'frequency' => $frequencies[$i] ?? null,
                'duration' => $durations[$i] ?? null,
                'instructions' => $instructions[$i] ?? null,
            ]);
        }

        audit('create', 'prescription', 'Membuat resep untuk client ID ' . $patientId);
        Session::flash('success', 'Resep berhasil dibuat.');
        $this->redirect('/patients/' . $patientId);
    }

    public function show(array $params): void
    {
        $prescription = Prescription::detail((int) $params['id']);
        if (!$prescription) { http_response_code(404); require __DIR__ . '/../Views/errors/404.php'; return; }

        $patient = \App\Models\Patient::find($prescription['patient_id']);
        $isOwner = Auth::roleName() === 'pasien' && (int) $patient['id'] === current_patient_id();
        $isPrescriber = (int) $prescription['psychiatrist_id'] === Auth::id();
        $isAdmin = in_array(Auth::roleName(), ['admin', 'super_admin'], true);
        if (!$isOwner && !$isPrescriber && !$isAdmin) {
            http_response_code(403); require __DIR__ . '/../Views/errors/403.php'; return;
        }

        $items = PrescriptionItem::forPrescription($prescription['id']);
        $this->view('prescription.show', compact('prescription', 'items'), 'print');
    }
}
