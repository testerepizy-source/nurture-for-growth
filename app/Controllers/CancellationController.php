<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\Consultation;
use App\Models\CancellationPolicy;
use App\Models\CancellationRequest;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Services\CommunicationService;

class CancellationController extends Controller
{
    public function create(array $params): void
    {
        $consultation = Consultation::detail((int) $params['id']);
        if (!$consultation) { http_response_code(404); require __DIR__ . '/../Views/errors/404.php'; return; }
        if (!$this->canAccess($consultation)) { http_response_code(403); require __DIR__ . '/../Views/errors/403.php'; return; }

        $hoursBefore = max(0, (strtotime($consultation['schedule_at']) - time()) / 3600);
        $refundPct = CancellationPolicy::refundPercentageFor($hoursBefore);
        $policies = CancellationPolicy::active();

        $this->view('cancellation.create', compact('consultation', 'hoursBefore', 'refundPct', 'policies'));
    }

    public function store(array $params): void
    {
        $this->requireCsrf();
        $consultation = Consultation::detail((int) $params['id']);
        if (!$consultation || !$this->canAccess($consultation)) {
            http_response_code(403); require __DIR__ . '/../Views/errors/403.php'; return;
        }
        if (CancellationRequest::forConsultation($consultation['id'])) {
            Session::flash('error', 'Sudah ada permintaan pembatalan untuk sesi ini.');
            $this->redirect('/consultation/' . $consultation['id']);
        }

        $hoursBefore = max(0, (strtotime($consultation['schedule_at']) - time()) / 3600);
        $refundPct = CancellationPolicy::refundPercentageFor($hoursBefore);
        $refundAmount = (int) round(($consultation['price_amount'] ?? 0) * $refundPct / 100);

        CancellationRequest::create([
            'consultation_id' => $consultation['id'],
            'requested_by' => Auth::id(),
            'reason' => $this->input('reason'),
            'hours_before_session' => (int) floor($hoursBefore),
            'refund_eligible_percentage' => $refundPct,
            'refund_amount' => $refundAmount,
            'status' => 'pending',
        ]);

        Consultation::update($consultation['id'], ['status' => 'cancelled']);
        audit('create', 'cancellation_request', 'Permintaan pembatalan booking ID ' . $consultation['id']);

        $patientUser = Patient::detailWithUser((int) $consultation['patient_id']) ?: [];
        $providerUser = User::find((int) $consultation['provider_id']) ?: [];
        $patientUserId = (int) ($patientUser['user_id'] ?? 0);
        $notifyUserId = (int) Auth::id() === $patientUserId
            ? (int) $consultation['provider_id']
            : $patientUserId;
        CommunicationService::notifyConsultationCancellation($consultation, $patientUser, $providerUser, $notifyUserId, (string) $this->input('reason'), $refundPct, $refundAmount);

        Session::flash('success', 'Permintaan pembatalan dikirim. Tim kami akan meninjau kelayakan refund dalam 1x24 jam.');
        $this->redirect('/consultation/' . $consultation['id']);
    }

    public function adminIndex(): void
    {
        $role = Auth::roleName();
        $providerId = in_array($role, ['psikolog', 'psikiater'], true) ? Auth::id() : null;
        $requests = CancellationRequest::pendingList($providerId);
        $this->view('admin.cancellations.index', ['requests' => $requests]);
    }

    public function approve(array $params): void
    {
        $this->requireCsrf();
        if (in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }
        $req = CancellationRequest::find((int) $params['id']);
        if ($req) {
            CancellationRequest::update($req['id'], [
                'status' => 'approved',
                'processed_by' => Auth::id(),
                'processed_at' => date('Y-m-d H:i:s'),
                'admin_note' => $this->input('admin_note'),
            ]);
            audit('approve', 'cancellation_request', 'Menyetujui refund untuk request ID ' . $req['id']);
            $this->notifyRefundDecision($req, 'approved');
            Session::flash('success', 'Permintaan pembatalan disetujui. Tandai "Sudah Diproses" setelah dana benar-benar dikembalikan.');
        }
        $this->redirect('/admin/cancellations');
    }

    public function reject(array $params): void
    {
        $this->requireCsrf();
        if (in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }
        $req = CancellationRequest::find((int) $params['id']);
        if ($req) {
            CancellationRequest::update($req['id'], [
                'status' => 'rejected',
                'processed_by' => Auth::id(),
                'processed_at' => date('Y-m-d H:i:s'),
                'admin_note' => $this->input('admin_note'),
            ]);
            audit('reject', 'cancellation_request', 'Menolak refund untuk request ID ' . $req['id']);
            $this->notifyRefundDecision($req, 'rejected');
        }
        Session::flash('success', 'Permintaan pembatalan ditolak.');
        $this->redirect('/admin/cancellations');
    }

    public function markProcessed(array $params): void
    {
        $this->requireCsrf();
        if (in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }
        $req = CancellationRequest::find((int) $params['id']);
        if ($req && $req['status'] === 'approved') {
            CancellationRequest::update($req['id'], ['status' => 'processed']);
            audit('update', 'cancellation_request', 'Refund ditandai selesai diproses, request ID ' . $req['id']);
        }
        Session::flash('success', 'Refund ditandai sudah diproses.');
        $this->redirect('/admin/cancellations');
    }

    private function notifyRefundDecision(array $req, string $decision): void
    {
        $consultation = Consultation::detail((int) $req['consultation_id']);
        if (!$consultation) {
            return;
        }
        $patientUser = Patient::detailWithUser((int) $consultation['patient_id']) ?: [];
        $providerUser = User::find((int) $consultation['provider_id']) ?: [];
        CommunicationService::notifyCancellationRefundDecision(
            $consultation,
            $patientUser,
            $providerUser,
            (int) $req['requested_by'],
            $decision,
            (string) $this->input('admin_note')
        );
    }

    private function canAccess(array $consultation): bool
    {
        if (Auth::roleName() === 'pasien') return (int) $consultation['patient_id'] === current_patient_id();
        return (int) $consultation['provider_id'] === Auth::id();
    }
}
