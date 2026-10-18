<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Invoice;
use App\Models\Consultation;

class InvoiceController extends Controller
{
    public function show(array $params): void
    {
        $invoice = Invoice::detail((int) $params['id']);
        if (!$invoice) { http_response_code(404); require __DIR__ . '/../Views/errors/404.php'; return; }

        $consultation = Consultation::find($invoice['consultation_id']);
        $isOwner = Auth::roleName() === 'pasien' && (int) $consultation['patient_id'] === current_patient_id();
        $isProvider = (int) $consultation['provider_id'] === Auth::id();
        $isAdmin = in_array(Auth::roleName(), ['admin', 'super_admin'], true);

        if (!$isOwner && !$isProvider && !$isAdmin) {
            http_response_code(403); require __DIR__ . '/../Views/errors/403.php'; return;
        }

        $reschedules = Invoice::getReschedulesForConsultation($invoice['consultation_id'], $invoice);
        $extensions = Invoice::getExtensions($invoice['consultation_id']);

        $this->view('invoice.show', [
            'invoice' => $invoice,
            'reschedules' => $reschedules,
            'extensions' => $extensions
        ], 'print');
    }
}
