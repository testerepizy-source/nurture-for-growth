<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\Consultation;
use App\Models\ConsultationReschedule;
use App\Models\ConsultationExtension;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Services\CommunicationService;
use App\Services\ConsultationModificationService;
use App\Services\MidtransService;

class PaymentController extends Controller
{
    /**
     * Display payment methods selection for consultation
     */
    public function consultationPayment(array $params): void
    {
        $consultation = Consultation::find((int) $params['id']);
        if (!$consultation || (int) $consultation['patient_id'] !== current_patient_id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        $amount = (int)($consultation['price_amount'] ?? 0);
        $method = (string)($consultation['payment_method'] ?? '');
        if ($method === 'midtrans' && ($consultation['payment_status'] ?? '') !== 'paid') {
            $this->startMidtransPayment('consultation', (int)$consultation['id'], $amount, 'Konsultasi NFG', '/consultation/' . $consultation['id']);
            return;
        }
        if (in_array($method, ['transfer', 'qris'], true) && ($consultation['payment_status'] ?? '') === 'unpaid') {
            $this->view('payment.instructions', [
                'referenceType' => 'konseling',
                'description'   => 'Konseling dengan ' . ($consultation['provider_name'] ?? 'psikolog') . ' — ' . format_datetime((string)$consultation['schedule_at']),
                'amount'        => $amount,
                'banks'         => \App\Models\PaymentChannel::activeBanks(),
                'qrisChannels'  => \App\Models\PaymentChannel::activeQris(),
                'formAction'    => base_url('/consultation/' . $consultation['id'] . '/payment/process'),
                'backUrl'       => base_url('/consultation/' . $consultation['id']),
                'selectedMethod'=> $method,
            ]);
            return;
        }
        $this->redirect('/consultation/' . $consultation['id']);
    }

    /**
     * Display payment methods selection for reschedule
     */
    public function reschedulePayment(array $params): void
    {
        $reschedule = ConsultationReschedule::find((int) $params['id']);
        if (!$reschedule) {
            http_response_code(404);
            require __DIR__.'/../Views/errors/404.php';
            return;
        }

        $original = Consultation::find($reschedule['original_consultation_id']);
        if (!$original || (int) $original['patient_id'] !== current_patient_id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        if (in_array((string) ($reschedule['payment_status'] ?? ''), ['pending_verification', 'paid'], true)) {
            $this->redirect('/consultation/' . (int) $original['id']);
            return;
        }

        $payment_methods = PaymentMethod::allActive();
        $amount = (int)($reschedule['rescheduling_fee'] ?? 0);
        $payment_description = 'Biaya Penjadwalan Ulang - ' . format_datetime($reschedule['new_schedule_at']);
        $form_action = '/reschedule/' . $reschedule['id'] . '/payment/process';
        $forceTransfer = false;
        $selectedPaymentMethod = $this->resolvePaymentMethod($reschedule['payment_method'] ?? '', $payment_methods, $forceTransfer);

        $original_booking_description = 'Booking awal: ' . format_datetime($original['schedule_at']) . ' dengan ' . ($original['provider_name'] ?? 'Provider');
        $original_price = (int)($original['price_amount'] ?? 0);

        $rescheduleIsPaidOrPending = false;
        if (!empty($reschedule) && is_array($reschedule)) {
            $status = $reschedule['payment_status'] ?? null;
            $method = $reschedule['payment_method'] ?? null;
            $paidAt = $reschedule['paid_at'] ?? null;
            $proof = $reschedule['payment_proof_path'] ?? null;
            $rescheduleIsPaidOrPending = (
                in_array($status, ['pending_verification', 'paid']) ||
                !empty($proof) ||
                !empty($method) ||
                !empty($paidAt)
            );
        }

        $this->view('payment.methods', compact('payment_methods', 'amount', 'payment_description', 'form_action', 'original_booking_description', 'original_price', 'reschedule', 'rescheduleIsPaidOrPending', 'selectedPaymentMethod', 'forceTransfer'));
    }

    /**
     * Display payment methods selection for extension
     */
    public function extensionPayment(array $params): void
    {
        $extension = ConsultationExtension::find((int) $params['id']);
        if (!$extension) {
            http_response_code(404);
            require __DIR__.'/../Views/errors/404.php';
            return;
        }

        $consultation = Consultation::find($extension['consultation_id']);
        if (!$consultation || (int) $consultation['patient_id'] !== current_patient_id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        if (in_array((string) ($extension['payment_status'] ?? ''), ['pending_verification', 'paid'], true)) {
            $this->redirect('/consultation/' . (int) $consultation['id']);
            return;
        }

        $payment_methods = PaymentMethod::allActive();
        $amount = (int)($extension['extension_price'] ?? 0);
        $payment_description = 'Biaya Perpanjangan Sesi - ' . $extension['extension_minutes'] . ' menit';
        $form_action = '/extension/' . $extension['id'] . '/payment/process';
        $forceTransfer = false;
        $selectedPaymentMethod = $this->resolvePaymentMethod($extension['payment_method'] ?? '', $payment_methods, $forceTransfer);

        $this->view('payment.methods', compact('payment_methods', 'amount', 'payment_description', 'form_action', 'selectedPaymentMethod', 'forceTransfer'));
    }

    /**
     * Process consultation payment
     */
    public function processConsultationPayment(array $params): void
    {
        $this->requireCsrf();
        $consultation = Consultation::find((int) $params['id']);
        
        if (!$consultation || (int) $consultation['patient_id'] !== current_patient_id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        $requested = trim((string) $this->input('payment_method', 'transfer'));
        if (in_array($requested, ['transfer', 'qris'], true)) {
            // Kanal manual (Transfer Bank / QRIS) selalu tersedia
            $paymentMethod = $requested;
        } else {
            $paymentMethod = $this->resolvePaymentMethod($requested, PaymentMethod::allActive(), false);
        }
        $amount = (int)($consultation['price_amount'] ?? 0);
        $isManual = in_array($paymentMethod, ['transfer', 'qris'], true);

        if (!$isManual) {
            $this->startMidtransPayment('consultation', (int)$consultation['id'], $amount, 'Konsultasi NFG', '/consultation/' . $consultation['id']);
            return;
        }

        // Bukti pembayaran wajib untuk kanal manual (Transfer/QRIS)
        $proofPath = $this->handlePaymentProofUpload('consultation', $consultation['id'], true);
        if (!$proofPath && $this->uploadError) {
            Session::flash('error', $this->uploadError);
            $this->redirect('/payment/consultation/' . $consultation['id']);
            return;
        }

        // Update consultation payment details
        Consultation::update($consultation['id'], [
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending_verification',
            'payment_proof_path' => $proofPath,
        ]);

        // Create payment transaction
        PaymentTransaction::create([
            'reference_type' => 'consultation',
            'reference_id' => $consultation['id'],
            'patient_id' => current_patient_id(),
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending_verification',
            'proof_path' => $proofPath,
        ]);

        audit('update', 'payment', 'Client mengunggah bukti pembayaran konsultasi ID ' . $consultation['id'] . ' via ' . ($paymentMethod === 'qris' ? 'QRIS' : 'transfer'));

        Session::flash('success', 'Bukti pembayaran diterima dan sedang diverifikasi. Anda akan menerima notifikasi setelah verifikasi selesai.');
        $this->redirect('/consultation/' . $consultation['id']);
    }

    /**
     * Process reschedule payment
     */
    public function processReschedulePayment(array $params): void
    {
        $this->requireCsrf();
        $reschedule = ConsultationReschedule::find((int) $params['id']);
        
        if (!$reschedule) {
            http_response_code(404);
            require __DIR__.'/../Views/errors/404.php';
            return;
        }

        $original = Consultation::find($reschedule['original_consultation_id']);
        if (!$original || (int) $original['patient_id'] !== current_patient_id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        $requested = trim((string) $this->input('payment_method', 'transfer'));
        if (in_array($requested, ['transfer', 'qris'], true)) {
            // Kanal manual (Transfer Bank / QRIS) selalu tersedia
            $paymentMethod = $requested;
        } else {
            $paymentMethod = $this->resolvePaymentMethod($requested, PaymentMethod::allActive(), false);
        }
        $amount = (int)($reschedule['rescheduling_fee'] ?? 0);
        $isManual = in_array($paymentMethod, ['transfer', 'qris'], true);

        if (!$isManual) {
            $this->startMidtransPayment('reschedule', (int)$reschedule['id'], $amount, 'Penjadwalan ulang NFG', '/consultation/' . $original['id']);
            return;
        }

        // Bukti pembayaran wajib untuk kanal manual (Transfer/QRIS)
        $proofPath = $this->handlePaymentProofUpload('reschedule', $reschedule['id'], true);
        if (!$proofPath && $this->uploadError) {
            Session::flash('error', $this->uploadError);
            $this->redirect('/payment/reschedule/' . $reschedule['id']);
            return;
        }

        // Update reschedule payment details
        ConsultationReschedule::update($reschedule['id'], [
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending_verification',
            'payment_proof_path' => $proofPath,
        ]);

        // Create payment transaction
        PaymentTransaction::create([
            'reference_type' => 'reschedule',
            'reference_id' => $reschedule['id'],
            'patient_id' => current_patient_id(),
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending_verification',
            'proof_path' => $proofPath,
        ]);

        // Ensure original consultation invoice exists for the base session amount
        $basePrice = (int)($original['price_amount'] ?? 0);
        if ($basePrice > 0) {
            $baseInvoice = Invoice::findByConsultationAndAmount($original['id'], $basePrice);
            if (!$baseInvoice) {
                Invoice::create([
                    'invoice_number' => Invoice::generateNumber(),
                    'consultation_id' => $original['id'],
                    'patient_id' => $original['patient_id'],
                    'amount' => $basePrice,
                ]);
            } elseif ((int)$baseInvoice['amount'] !== $basePrice) {
                Invoice::update($baseInvoice['id'], ['amount' => $basePrice]);
            }
        }

        audit('update', 'payment', 'Client melakukan pembayaran penjadwalan ulang ID ' . $reschedule['id'] . ' via ' . ($paymentMethod === 'qris' ? 'QRIS' : $paymentMethod));

        Session::flash('success', 'Bukti pembayaran diterima dan sedang diverifikasi. Anda akan menerima notifikasi setelah verifikasi selesai.');
        $this->redirect('/consultation/' . $original['id']);
    }

    public function verifyReschedulePayment(array $params): void
    {
        $this->requireCsrf();
        $reschedule = ConsultationReschedule::find((int) $params['id']);
        if (!$reschedule) {
            http_response_code(404);
            require __DIR__.'/../Views/errors/404.php';
            return;
        }

        $original = Consultation::find($reschedule['original_consultation_id']);
        if (!$original || (int) $original['provider_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        if ($reschedule['payment_status'] === 'pending_verification') {
            ConsultationReschedule::update($reschedule['id'], [
                'payment_status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
            ]);
            PaymentTransaction::raw('UPDATE payment_transactions SET payment_status = ? WHERE reference_type = ? AND reference_id = ?', ['paid', 'reschedule', $reschedule['id']]);
            $basePrice = (int)($original['price_amount'] ?? 0);
            if ($basePrice > 0) {
                $baseInvoice = Invoice::findByConsultationAndAmount($original['id'], $basePrice);
                if (!$baseInvoice) {
                    Invoice::create([
                        'invoice_number' => Invoice::generateNumber(),
                        'consultation_id' => $original['id'],
                        'patient_id' => $original['patient_id'],
                        'amount' => $basePrice,
                    ]);
                } elseif ((int)$baseInvoice['amount'] !== $basePrice) {
                    Invoice::update($baseInvoice['id'], ['amount' => $basePrice]);
                }
            }
            audit('update', 'payment', 'Verifikasi pembayaran reschedule ID ' . $reschedule['id']);
            Session::flash('success', 'Pembayaran reschedule berhasil diverifikasi.');
        } else {
            Session::flash('error', 'Status pembayaran tidak memerlukan verifikasi.');
        }

        $this->redirect('/consultation/' . $original['id']);
    }

    public function verifyExtensionPayment(array $params): void
    {
        $this->requireCsrf();
        $extension = ConsultationExtension::find((int) $params['id']);
        if (!$extension) {
            http_response_code(404);
            require __DIR__.'/../Views/errors/404.php';
            return;
        }

        $consultation = Consultation::find($extension['consultation_id']);
        if (!$consultation || (int) $consultation['provider_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        if ($extension['payment_status'] === 'pending_verification') {
            ConsultationExtension::update($extension['id'], [
                'payment_status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
            ]);
            PaymentTransaction::raw('UPDATE payment_transactions SET payment_status = ? WHERE reference_type = ? AND reference_id = ?', ['paid', 'extension', $extension['id']]);

            $patient = Patient::detailWithUser((int) $consultation['patient_id']) ?: [];
            $providerUser = User::find(Auth::id()) ?: [];
            CommunicationService::notifyExtensionPaymentVerified(
                Consultation::detail($consultation['id']),
                $patient,
                $providerUser,
                (int) $extension['id']
            );

            $approved = ConsultationModificationService::approveExtension((int) $extension['id'], Auth::id());
            if ($approved) {
                CommunicationService::notifyExtensionApproved(
                    Consultation::detail($consultation['id']),
                    $patient,
                    $providerUser,
                    (int) ($extension['extension_minutes'] ?? 0),
                    (int) ($extension['extension_price'] ?? 0)
                );
                audit('approve', 'consultation_extension', 'Menyetujui extension ID ' . $extension['id'] . ' via verifikasi pembayaran');
                Session::flash('success', 'Pembayaran diverifikasi dan perpanjangan disetujui. Durasi sesi telah diperbarui.');
            } else {
                audit('update', 'payment', 'Verifikasi pembayaran extension ID ' . $extension['id']);
                Session::flash('success', 'Pembayaran perpanjangan berhasil diverifikasi.');
            }
        } else {
            Session::flash('error', 'Status pembayaran tidak memerlukan verifikasi.');
        }

        $this->redirect('/consultation/' . $consultation['id']);
    }

    /**
     * Process extension payment
     */
    public function processExtensionPayment(array $params): void
    {
        $this->requireCsrf();
        $extension = ConsultationExtension::find((int) $params['id']);
        
        if (!$extension) {
            http_response_code(404);
            require __DIR__.'/../Views/errors/404.php';
            return;
        }

        $consultation = Consultation::find($extension['consultation_id']);
        if (!$consultation || (int) $consultation['patient_id'] !== current_patient_id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        $requested = trim((string) $this->input('payment_method', 'transfer'));
        if (in_array($requested, ['transfer', 'qris'], true)) {
            // Kanal manual (Transfer Bank / QRIS) selalu tersedia
            $paymentMethod = $requested;
        } else {
            $paymentMethod = $this->resolvePaymentMethod($requested, PaymentMethod::allActive(), false);
        }
        $amount = (int)($extension['extension_price'] ?? 0);
        $isManual = in_array($paymentMethod, ['transfer', 'qris'], true);

        if (!$isManual) {
            $this->startMidtransPayment('extension', (int)$extension['id'], $amount, 'Perpanjangan sesi NFG', '/consultation/' . $consultation['id']);
            return;
        }

        // Bukti pembayaran wajib untuk kanal manual (Transfer/QRIS)
        $proofPath = $this->handlePaymentProofUpload('extension', $extension['id'], true);
        if (!$proofPath && $this->uploadError) {
            Session::flash('error', $this->uploadError);
            $this->redirect('/payment/extension/' . $extension['id']);
            return;
        }

        // Update extension payment details
        ConsultationExtension::update($extension['id'], [
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending_verification',
            'payment_proof_path' => $proofPath,
        ]);

        // Create payment transaction
        PaymentTransaction::create([
            'reference_type' => 'extension',
            'reference_id' => $extension['id'],
            'patient_id' => current_patient_id(),
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending_verification',
            'proof_path' => $proofPath,
        ]);

        audit('update', 'payment', 'Client melakukan pembayaran perpanjangan ID ' . $extension['id'] . ' via ' . ($paymentMethod === 'qris' ? 'QRIS' : $paymentMethod));

        Session::flash('success', 'Bukti pembayaran diterima dan sedang diverifikasi. Anda akan menerima notifikasi setelah verifikasi selesai.');
        $this->redirect('/consultation/' . $consultation['id']);
    }

    private function startMidtransPayment(string $referenceType, int $referenceId, int $amount, string $description, string $successPath): void
    {
        if ($amount < 1) {
            Session::flash('error', 'Nominal pembayaran tidak valid.');
            $this->redirect($successPath);
        }

        $user = Auth::user() ?? [];
        $pending = PaymentTransaction::latestPending($referenceType, $referenceId);
        $orderId = (string)($pending['transaction_id'] ?? '');
        $snapToken = (string)($pending['snap_token'] ?? '');
        $finishUrl = '';
        $redirectUrl = $snapToken !== '' ? MidtransService::hostedRedirectUrl($snapToken) : '';

        try {
            if ($orderId === '' || $snapToken === '') {
                $orderId = 'NFG-' . strtoupper($referenceType) . '-' . $referenceId . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));
                $finishUrl = request_absolute_url('/payment/midtrans/finish?order_id=' . rawurlencode($orderId));
                $snap = MidtransService::createSnapToken(['order_id' => $orderId, 'gross_amount' => $amount, 'reference_type' => $referenceType, 'description' => $description, 'customer_name' => $user['name'] ?? 'NFG Patient', 'customer_email' => $user['email'] ?? null, 'customer_phone' => $user['phone'] ?? null, 'finish_url' => $finishUrl]);
                $snapToken = $snap['token'];
                $redirectUrl = $snap['redirect_url'] ?: MidtransService::hostedRedirectUrl($snapToken);
            }
            if ($finishUrl === '') $finishUrl = request_absolute_url('/payment/midtrans/finish?order_id=' . rawurlencode($orderId));

            $transactionData = PaymentTransaction::filterColumns([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'patient_id' => current_patient_id(),
                'amount' => $amount,
                'payment_method' => 'midtrans',
                'payment_status' => 'pending_verification',
                'transaction_id' => $orderId,
                'snap_token' => $snapToken,
                'notes' => 'midtrans_environment:' . (env('MIDTRANS_IS_PRODUCTION', '0') === '1' ? 'production' : 'sandbox'),
            ]);
            if ($pending) PaymentTransaction::update($pending['id'], $transactionData);
            else PaymentTransaction::create($transactionData);
            $this->setPaymentPending($referenceType, $referenceId);
            $this->view('payment.midtrans', [
                'snapToken' => $snapToken,
                'clientKey' => env('MIDTRANS_CLIENT_KEY', ''),
                'finishUrl' => $finishUrl,
                'redirectUrl' => $redirectUrl,
                'successPath' => base_url($successPath),
                'amount' => $amount,
            ], null);
        } catch (\Throwable $exception) {
            error_log('Midtrans payment error: ' . $exception->getMessage());
            Session::flash('error', 'Pembayaran belum dapat dibuat. Periksa konfigurasi Midtrans atau coba lagi.');
            $this->redirect($successPath);
        }
    }

    private function setPaymentPending(string $referenceType, int $referenceId): void
    {
        $data = ['payment_method' => 'midtrans', 'payment_status' => 'pending_verification'];
        if ($referenceType === 'consultation') Consultation::update($referenceId, $data);
        if ($referenceType === 'reschedule') ConsultationReschedule::update($referenceId, $data);
        if ($referenceType === 'extension') ConsultationExtension::update($referenceId, $data);
    }

    public function midtransNotification(): void
    {
        $notification = json_decode(file_get_contents('php://input'), true);
        if (!is_array($notification) || !MidtransService::isValidNotification($notification)) {
            $this->json(['error' => 'Invalid notification'], 403);
        }
        $transaction = PaymentTransaction::findBy('transaction_id', (string)($notification['order_id'] ?? ''));
        if (!$transaction) $this->json(['error' => 'Transaction not found'], 404);
        if ((int)round((float)($notification['gross_amount'] ?? 0)) !== (int)$transaction['amount']) $this->json(['error' => 'Amount mismatch'], 400);
        $gatewayStatus = (string)($notification['transaction_status'] ?? '');
        $paymentStatus = in_array($gatewayStatus, ['settlement', 'capture'], true) ? 'paid' : (in_array($gatewayStatus, ['deny', 'cancel', 'expire'], true) ? 'failed' : 'pending_verification');
        PaymentTransaction::update($transaction['id'], ['payment_status' => $paymentStatus]);
        $this->applyGatewayStatus($transaction, $paymentStatus);
        $this->json(['ok' => true]);
    }

    public function midtransFinish(): void
    {
        $orderId = (string)$this->input('order_id', '');
        $transaction = PaymentTransaction::findBy('transaction_id', $orderId);
        if ($transaction && $transaction['payment_status'] === 'pending_verification') {
            try {
                $status = MidtransService::getStatus($orderId);
                if (in_array($status['transaction_status'] ?? '', ['settlement', 'capture'], true)) {
                    PaymentTransaction::update($transaction['id'], ['payment_status' => 'paid']);
                    $this->applyGatewayStatus($transaction, 'paid');
                }
            } catch (\Throwable $exception) { error_log('Midtrans status check error: ' . $exception->getMessage()); }
        }
        Session::flash('success', 'Status pembayaran sedang diperbarui.');
        $this->redirect($transaction && $transaction['reference_type'] === 'consultation' ? '/consultation/' . $transaction['reference_id'] : '/consultation');
    }

    private function applyGatewayStatus(array $transaction, string $paymentStatus): void
    {
        $status = $paymentStatus === 'paid' ? 'paid' : ($paymentStatus === 'failed' ? 'unpaid' : 'pending_verification');
        $data = ['payment_status' => $status];
        if ($transaction['reference_type'] === 'consultation') Consultation::update($transaction['reference_id'], $data);
        if ($transaction['reference_type'] === 'reschedule') ConsultationReschedule::update($transaction['reference_id'], $data + ($paymentStatus === 'paid' ? ['paid_at' => date('Y-m-d H:i:s')] : []));
        if ($transaction['reference_type'] === 'extension') ConsultationExtension::update($transaction['reference_id'], $data + ($paymentStatus === 'paid' ? ['paid_at' => date('Y-m-d H:i:s')] : []));
    }

    private ?string $uploadError = null;

    private function resolvePaymentMethod(string $requestedMethod, array $availableMethods, bool $forceTransfer = false): string
    {
        $activeCodes = array_values(array_unique(array_filter(array_map(static function ($method): string {
            return trim((string)($method['code'] ?? ''));
        }, $availableMethods), static function (string $code): bool {
            return $code !== '';
        })));

        if ($forceTransfer) {
            return in_array('transfer', $activeCodes, true) ? 'transfer' : ($activeCodes[0] ?? 'transfer');
        }

        $requestedMethod = trim($requestedMethod);
        if ($requestedMethod !== '' && in_array($requestedMethod, $activeCodes, true)) {
            return $requestedMethod;
        }

        return $activeCodes[0] ?? 'transfer';
    }

    /**
     * Handle payment proof upload
     */
    private function handlePaymentProofUpload(string $type, int $id, bool $required = false): ?string
    {
        if (empty($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                $this->uploadError = 'Bukti pembayaran wajib diunggah untuk pembayaran Transfer Bank/QRIS.';
            }
            return null;
        }

        $file = $_FILES['payment_proof'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Gagal mengunggah bukti pembayaran. Silakan coba lagi.';
            return null;
        }

        if ($file['size'] > 3 * 1024 * 1024) {
            $this->uploadError = 'Ukuran bukti pembayaran maksimal 3MB.';
            return null;
        }

        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMime[$mime])) {
            $this->uploadError = 'Format bukti pembayaran harus JPG, PNG, atau PDF.';
            return null;
        }

        $dir = __DIR__ . '/../../public/assets/uploads/payments';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $filename = 'pay-' . $type . '-' . $id . '-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->uploadError = 'Gagal menyimpan bukti pembayaran ke server.';
            return null;
        }

        return 'assets/uploads/payments/' . $filename;
    }
}
