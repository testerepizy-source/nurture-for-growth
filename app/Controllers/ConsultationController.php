<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\Consultation;
use App\Models\ConsultationNote;
use App\Models\FollowUpPlan;
use App\Models\Psychologist;
use App\Models\Psychiatrist;
use App\Models\VideoSession;
use App\Models\SessionAttendance;
use App\Models\ChatMessage;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Models\ConcernTag;
use App\Models\ProviderReview;
use App\Models\Schedule;
use App\Models\ConsultationDocument;
use App\Models\CalendarSyncLog;
use App\Services\PriceCalculatorService;
use App\Services\PromoService;
use App\Services\ConsultationModificationService;
use App\Models\ConsultationReschedule;
use App\Models\ConsultationExtension;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\InformedConsent;
use App\Models\CancellationPolicy;
use App\Models\CancellationRequest;
use App\Services\CommunicationService;
use App\Services\GoogleMeetService;

class ConsultationController extends Controller
{
    public function index(): void
    {
        $role = Auth::roleName();
        if ($role === 'pasien') {
            $status = $this->input('status', '');
            $allList = Consultation::forPatient(current_patient_id());
            $allList = array_values(array_filter($allList, function ($consultation) {
                return !ConsultationReschedule::isDuplicateTargetConsultation($consultation);
            }));
            $statusCounts = [
                'all' => count($allList),
                'pending' => 0,
                'approved' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'rejected' => 0,
                'reschedule_pending' => 0,
            ];
            foreach ($allList as $consultation) {
                $statusKey = strtolower((string) ($consultation['status'] ?? ''));
                if (isset($statusCounts[$statusKey])) {
                    $statusCounts[$statusKey]++;
                }
                if (ConsultationReschedule::getPending($consultation['id'])) {
                    $statusCounts['reschedule_pending']++;
                }
            }

            $list = $allList;
            if ($status !== '') {
                $filtered = [];
                foreach ($list as $consultation) {
                    if ($status === 'reschedule_pending') {
                        if (ConsultationReschedule::getPending($consultation['id'])) {
                            $filtered[] = $consultation;
                        }
                    } elseif (strtolower((string) ($consultation['status'] ?? '')) === $status) {
                        $filtered[] = $consultation;
                    }
                }
                $list = $filtered;
            }

            $this->view('consultation.index_patient', ['list' => $list, 'status' => $status, 'statusCounts' => $statusCounts]);
        } else {
            $status = $this->input('status', '');
            $list = Consultation::forProvider(Auth::id(), $status ?: null, true);
            $list = array_values(array_filter($list, function ($consultation) {
                return !ConsultationReschedule::isDuplicateTargetConsultation($consultation);
            }));
            $this->view('consultation.index_provider', ['list' => $list, 'status' => $status]);
        }
    }

    public function create(): void
    {
        $patientId = current_patient_id();
        if ($this->input('consent_checked') !== '1') {
            $this->view('consultation.consent_prompt', [
                'providerId' => (int) $this->input('provider_id', 0),
                'isMatched' => (bool) $this->input('matched', false),
                'hasConsent' => (bool) InformedConsent::forPatient($patientId),
            ]);
            return;
        }
        Session::set('informed_consent_acknowledged_' . $patientId, true);

        $psychologists = Psychologist::allWithUser();
        $psychiatrists = Psychiatrist::allWithUser();
        $preselectedProviderId = (int) $this->input('provider_id', 0);
        $isMatched = (bool) $this->input('matched', false);

        $selectedProvider = null;
        $providerTier = 'associate';
        $providerSchedules = [];
        $availableSlots = [];
        if ($preselectedProviderId) {
            $user = User::find($preselectedProviderId);
            $psy = Psychologist::byUserId($preselectedProviderId);
            $psi = Psychiatrist::byUserId($preselectedProviderId);
            $record = $psy ?: $psi;
            if ($user && $record) {
                $reviewSummary = ProviderReview::summaryFor($preselectedProviderId);
                $selectedProvider = array_merge($record, [
                    'name' => $user['name'],
                    'phone' => $user['phone'],
                    'email' => $user['email'],
                    'photo' => $user['photo'],
                    'role_label' => $psy ? 'Psikolog' : 'Psikiater',
                    'tags' => ConcernTag::tagsForProvider($preselectedProviderId),
                    'review' => $reviewSummary,
                ]);
                $providerTier = $record['tier'] ?? 'associate';
                $providerSchedules = Schedule::forProvider($preselectedProviderId);
                $availableSlots = Schedule::getAvailableSlots($preselectedProviderId, 30);
            }
        }

        $rateTable = PriceCalculatorService::tableFor($providerTier);
        $activePromos = PromoService::activeForBookingWindow($preselectedProviderId ?: null);
        $paymentMethods = PaymentMethod::allActive();
        if (empty($paymentMethods)) {
            $paymentMethods = [
                ['code' => 'transfer', 'name' => 'Transfer Bank', 'description' => 'Bayar melalui transfer bank.', 'is_active' => 1],
                ['code' => 'card', 'name' => 'Kartu Kredit/Debit', 'description' => 'Bayar dengan kartu kredit atau debit.', 'is_active' => 1],
                ['code' => 'e_wallet', 'name' => 'E-Wallet', 'description' => 'Bayar menggunakan e-wallet populer.', 'is_active' => 1],
                ['code' => 'cash', 'name' => 'Tunai', 'description' => 'Bayar tunai di loket klinik.', 'is_active' => 1],
            ];
        }
        $selectedPaymentMethod = 'transfer';

        $providerCards = [];
        $allProviderUserIds = [];
        foreach ($psychologists as $p) {
            $allProviderUserIds[] = (int) $p['user_id'];
            $providerCards[] = [
                'id' => (int) $p['user_id'],
                'name' => $p['name'] ?? '',
                'role' => 'Psikolog',
                'specialization' => $p['specialization'] ?? '',
                'photo' => $p['photo'] ?? null,
                'tier' => $p['tier'] ?? 'associate',
            ];
        }
        foreach ($psychiatrists as $p) {
            $allProviderUserIds[] = (int) $p['user_id'];
            $providerCards[] = [
                'id' => (int) $p['user_id'],
                'name' => $p['name'] ?? '',
                'role' => 'Psikiater',
                'specialization' => $p['specialization'] ?? '',
                'photo' => $p['photo'] ?? null,
                'tier' => $p['tier'] ?? 'associate',
            ];
        }
        $reviewMap = ProviderReview::summaryForMany($allProviderUserIds);
        foreach ($providerCards as &$card) {
            $table = PriceCalculatorService::tableFor($card['tier']);
            $card['price_from'] = (int) min($table['individual']['online'], $table['individual']['tatap_muka']);
            $card['review'] = $reviewMap[$card['id']] ?? ['total' => 0, 'average' => 0];
        }
        unset($card);

        $this->view('consultation.create', compact('psychologists', 'psychiatrists', 'preselectedProviderId', 'isMatched', 'selectedProvider', 'rateTable', 'providerSchedules', 'availableSlots', 'paymentMethods', 'selectedPaymentMethod', 'activePromos', 'providerCards'));
    }

    public function consent(): void
    {
        $patient = Patient::detailWithUser(current_patient_id());
        if (!$patient) {
            Session::flash('error', 'Profil client belum lengkap.');
            $this->redirect('/profile');
            return;
        }

        $informedConsent = InformedConsent::forPatient(current_patient_id());
        $this->view('consultation.consent', [
            'patient' => $patient,
            'informedConsent' => $informedConsent,
            'providerId' => (int) $this->input('provider_id', 0),
            'isMatched' => (bool) $this->input('matched', false),
        ]);
    }

    public function consentStore(): void
    {
        $this->requireCsrf();
        $patientId = current_patient_id();
        if (InformedConsent::forPatient($patientId)) {
            $this->redirect($this->consultationCreatePath());
            return;
        }

        $requiredChecks = [
            'understands_process', 'understands_benefits', 'understands_confidentiality',
            'understands_data', 'understands_assessment', 'understands_emergency',
            'understands_rights', 'understands_administration',
        ];
        foreach ($requiredChecks as $field) {
            if ($this->input($field) !== '1') {
                Session::flash('error', 'Mohon centang seluruh pernyataan persetujuan sebelum melanjutkan.');
                $this->redirect('/consultation/consent?provider_id=' . (int) $this->input('provider_id', 0));
                return;
            }
        }

        $fullName = $this->input('full_name');
        $signatureName = $this->input('signature_name');
        if (!$fullName || !$signatureName) {
            Session::flash('error', 'Nama lengkap dan tanda tangan elektronik wajib diisi.');
            $this->redirect('/consultation/consent?provider_id=' . (int) $this->input('provider_id', 0));
            return;
        }

        InformedConsent::create([
            'patient_id' => $patientId,
            'full_name' => $fullName,
            'birth_date' => $this->input('birth_date') ?: null,
            'guardian_name' => $this->input('guardian_name') ?: null,
            'phone' => $this->input('phone') ?: null,
            'understands_process' => 1,
            'understands_benefits' => 1,
            'understands_confidentiality' => 1,
            'understands_data' => 1,
            'understands_assessment' => 1,
            'understands_emergency' => 1,
            'understands_rights' => 1,
            'understands_administration' => 1,
            'signature_name' => $signatureName,
            'signed_at' => date('Y-m-d H:i:s'),
            'signed_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        Session::flash('success', 'Informed consent berhasil disimpan. Anda dapat melanjutkan booking konseling.');
        $this->redirect($this->consultationCreatePath());
    }

    private function consultationCreatePath(): string
    {
        $query = [];
        $providerId = (int) $this->input('provider_id', 0);
        if ($providerId) $query['provider_id'] = $providerId;
        if ($this->input('matched') === '1') $query['matched'] = '1';
        $query['consent_checked'] = '1';
        return '/consultation/create?' . http_build_query($query);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $patientId = current_patient_id();
        $hasConsentAcknowledgement = Session::get('informed_consent_acknowledged_' . $patientId, false);
        if (!InformedConsent::forPatient($patientId) && !$hasConsentAcknowledgement) {
            Session::flash('error', 'Informed consent wajib diisi sebelum booking konseling pertama.');
            $this->redirect('/consultation/consent');
            return;
        }
        $providerId = (int) $this->input('provider_id');
        $method = $this->input('method', 'video');
        $sessionType = $this->input('session_type', 'individual');
        $paymentMethod = $this->input('payment_method', 'transfer');
        $patientRole = $this->input('patient_role', 'self');
        $scheduleAt = $this->input('schedule_at');

        $validRoles = ['self', 'husband', 'wife', 'father', 'mother', 'child'];
        if (!in_array($patientRole, $validRoles, true)) {
            $patientRole = 'self';
        }
        if ($sessionType === 'individual') {
            $patientRole = 'self';
        } elseif ($sessionType === 'couple' && !in_array($patientRole, ['husband', 'wife'], true)) {
            $patientRole = 'husband';
        } elseif ($sessionType === 'family' && !in_array($patientRole, ['father', 'mother', 'child'], true)) {
            $patientRole = 'father';
        }

        $availableDatetimes = array_column(Schedule::getAvailableSlots($providerId, 30, $method === 'tatap_muka' ? 'offline' : 'online'), 'datetime');
        if (!$providerId || !$scheduleAt || !in_array($scheduleAt, $availableDatetimes, true)) {
            Session::flash('error', 'Tanggal dan jam yang dipilih tidak tersedia. Silakan pilih slot praktik yang tersedia.');
            $this->redirect('/consultation/create?provider_id=' . $providerId);
            return;
        }

        $psy = Psychologist::byUserId($providerId);
        $psi = Psychiatrist::byUserId($providerId);
        $tier = ($psy['tier'] ?? null) ?: ($psi['tier'] ?? 'associate');
        $originalPrice = PriceCalculatorService::calculate($tier, $sessionType, $method);

        // Terapkan promo/diskon jika tanggal sesi masuk periode promo.
        $promo = PromoService::resolve($providerId, substr((string) $scheduleAt, 0, 10));
        [$price, $discountAmount] = PromoService::apply($originalPrice, $promo);
        $promoId = $promo['id'] ?? null;

        $paymentProofPath = null;
        // Kanal manual (transfer/qris): bukti diunggah setelah booking di halaman instruksi pembayaran.

        $previousSession = $this->input('previous_session', 'no') === 'yes' ? 'yes' : 'no';
        $previousSessionDocumentPath = null;
        $previousSessionDocumentName = null;

        if ($previousSession === 'yes') {
            $previousUpload = $this->handlePreviousSessionDocumentUpload();
            if ($this->uploadError) {
                Session::flash('error', $this->uploadError);
                $this->redirect('/consultation/create?provider_id=' . $providerId);
                return;
            }
            if ($previousUpload) {
                $previousSessionDocumentPath = $previousUpload['path'];
                $previousSessionDocumentName = $previousUpload['name'];
            }
        }

        $paymentStatus = 'unpaid';
        if ($paymentMethod === 'midtrans') {
            $paymentStatus = 'pending_verification';
        } elseif (in_array($paymentMethod, ['card', 'e_wallet'], true)) {
            $paymentStatus = 'paid';
        }
        // transfer & qris: tetap unpaid sampai client mengunggah bukti di halaman instruksi pembayaran

        $durationMinutes = (int) $this->input('duration_minutes', 0);
        if ($durationMinutes < 15) {
            $durationMinutes = Schedule::getSlotDurationForDateTime($providerId, $scheduleAt);
        }

        $id = Consultation::create([
            'patient_id'   => current_patient_id(),
            'provider_id'  => $providerId,
            'schedule_at'  => $scheduleAt,
            'duration_minutes' => $durationMinutes,
            'method'       => $method,
            'session_type' => $sessionType,
            'patient_role' => $patientRole,
            'reason'       => $this->input('reason'),
            'previous_session' => $previousSession,
            'previous_session_document_name' => $previousSessionDocumentName,
            'previous_session_document_path' => $previousSessionDocumentPath,
            'status'       => 'pending',
            'price_amount' => $price,
            'original_price_amount' => $originalPrice,
            'discount_amount' => $discountAmount,
            'promo_id'     => $promoId,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'payment_proof_path' => $paymentProofPath,
        ]);
        if ($this->input('from_matching')) {
            (new \App\Services\SmartMatchingService())->recordSelection(current_patient_id(), $providerId);
        }

        Notification::create([
            'user_id' => $providerId,
            'title' => 'Booking Konseling Baru',
            'message' => 'Ada permintaan booking konseling baru yang menunggu persetujuan Anda.',
            'type' => 'booking',
            'url' => base_url('/consultation/' . $id),
        ]);

        if ($paymentMethod === 'midtrans') {
            audit('create', 'consultation', 'Client membuat booking konseling ID ' . $id);
            Session::flash('success', 'Booking berhasil dibuat. Silakan lanjutkan pembayaran melalui Midtrans.');
            $this->redirect('/payment/consultation/' . $id);
            return;
        }

        $patient = Patient::detailWithUser(current_patient_id());
        $provider = User::find($providerId);
        $providerProfile = $psy ?: $psi ?: [];
        $meetingLink = null;
        $bookingStatus = 'pending';
        if ($patient && $provider && in_array($method, ['video', 'voice', 'chat'], true)) {
            // Generate Google Meet link via Google Calendar segera saat booking dibuat.
            // GoogleMeetService memakai kredensial dari .env/integration_configs,
            // dan fail-soft (meeting tetap bisa berjalan tanpa Google) jika belum terkonfigurasi.
            $meetingLink = GoogleMeetService::createMeeting([
                'id' => $id,
                'schedule_at' => $scheduleAt,
                'duration_minutes' => $durationMinutes,
            ], $patient, $provider);
            // The internal waiting room is NOT stored as meeting_link; the link is only
            // set when the provider enters one or Google Meet is successfully created.
            Consultation::update($id, ['meeting_link' => $meetingLink]);
            if ($method === 'video' && !VideoSession::byConsultation($id)) {
                VideoSession::create(['consultation_id' => $id, 'room_code' => bin2hex(random_bytes(6)), 'status' => 'waiting']);
            }
            if (str_contains((string) $meetingLink, 'meet.google.com')) {
                CalendarSyncLog::create(['user_id' => $patient['user_id'], 'consultation_id' => $id, 'provider' => 'google_meet']);
                CalendarSyncLog::create(['user_id' => $providerId, 'consultation_id' => $id, 'provider' => 'google_meet']);
            }
        }
        if ($patient && $provider) {
            CommunicationService::notifyConsultationBooking([
                'id' => $id,
                'schedule_at' => $this->input('schedule_at'),
                'method' => $method,
                'session_type' => $sessionType,
                'reason' => $this->input('reason'),
                'price_amount' => $price,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'payment_proof_path' => $paymentProofPath,
                'meeting_link' => $meetingLink,
                'status' => $bookingStatus,
            ], $patient, $provider, $providerProfile);
        }

        audit('create', 'consultation', 'Client membuat booking konseling ID ' . $id);
        if (in_array($paymentMethod, ['transfer', 'qris'], true)) {
            Session::flash('success', 'Booking berhasil dibuat. Silakan selesaikan pembayaran dan unggah bukti pembayaran Anda.');
            $this->redirect('/payment/consultation/' . $id);
            return;
        }
        Session::flash('success', $bookingStatus === 'approved'
            ? 'Booking berhasil dibuat dan link meeting sudah siap digunakan.'
            : 'Permintaan booking berhasil dikirim, menunggu persetujuan' . ($paymentProofPath ? ' dan pembayaran akan diverifikasi tim kami.' : '.'));
        $this->redirect('/consultation/' . $id);
    }

    private ?string $uploadError = null;

    /**
     * Tangani unggah bukti transfer. Mengembalikan path relatif jika berhasil,
     * atau null jika tidak ada file (pembayaran dianggap menyusul / offline).
     */
    private function handlePaymentProofUpload(bool $required = false): ?string
    {
        if (empty($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                $this->uploadError = 'Unggah bukti transfer wajib untuk metode pembayaran transfer.';
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

        $filename = 'pay-' . current_patient_id() . '-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->uploadError = 'Gagal menyimpan bukti pembayaran ke server.';
            return null;
        }

        return 'assets/uploads/payments/' . $filename;
    }

    private function handlePreviousSessionDocumentUpload(): ?array
    {
        if (empty($_FILES['previous_session_document']) || $_FILES['previous_session_document']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['previous_session_document'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Gagal mengunggah dokumen sesi sebelumnya. Silakan coba lagi.';
            return null;
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            $this->uploadError = 'Ukuran dokumen maksimal 10MB.';
            return null;
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMime[$mime])) {
            $this->uploadError = 'Format dokumen harus JPG, PNG, PDF, DOC, atau DOCX.';
            return null;
        }

        $dir = __DIR__ . '/../../public/assets/uploads/consultation_previous_documents';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $filename = 'consult-' . current_patient_id() . '-prev-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
        $destination = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->uploadError = 'Gagal menyimpan dokumen sesi sebelumnya ke server.';
            return null;
        }

        return [
            'path' => 'assets/uploads/consultation_previous_documents/' . $filename,
            'name' => $file['name'],
        ];
    }

    public function show(array $params): void
    {
        $consultation = Consultation::detail((int) $params['id']);
        if (!$consultation) { http_response_code(404); require __DIR__.'/../Views/errors/404.php'; return; }
        if (!$this->canAccess($consultation)) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }

        $duplicateOriginalId = ConsultationReschedule::findOriginalConsultationIdForDuplicateTarget($consultation);
        if (!empty($duplicateOriginalId) && $duplicateOriginalId !== (int) $consultation['id']) {
            Session::flash('info', 'Sesi ini adalah penjadwalan ulang dari sesi ID ' . $duplicateOriginalId . '. Menampilkan sesi asli.');
            $this->redirect('/consultation/' . $duplicateOriginalId);
            return;
        }

        $patientUser = Patient::detailWithUser($consultation['patient_id']);
        $providerUser = User::find($consultation['provider_id']);

        $notes = ConsultationNote::forConsultation($consultation['id']);
        $followUps = FollowUpPlan::forConsultation($consultation['id']);
        $videoSession = VideoSession::byConsultation($consultation['id']);
        $chats = ChatMessage::forConsultation($consultation['id']);
        $existingReview = \App\Models\ProviderReview::rawOne('SELECT * FROM provider_reviews WHERE consultation_id = ?', [$consultation['id']]);
        $documents = ConsultationDocument::forConsultation($consultation['id']);
        $pendingReschedule = ConsultationReschedule::getPending($consultation['id']);
        $completedReschedule = ConsultationReschedule::getLastCompleted($consultation['id']);
        $pendingExtension = ConsultationExtension::getPending($consultation['id']);

        $providerProfile = [];
        if ($providerUser) {
            $providerProfile = Psychologist::byUserId($providerUser['id']) ?: Psychiatrist::byUserId($providerUser['id']) ?: [];
        }

        $this->view('consultation.show', compact(
            'consultation',
            'patientUser',
            'providerUser',
            'providerProfile',
            'notes',
            'followUps',
            'videoSession',
            'chats',
            'existingReview',
            'documents',
            'pendingReschedule',
            'completedReschedule',
            'pendingExtension'
        ));
    }

    public function approve(array $params): void
    {
        $this->requireCsrf();
        $c = Consultation::find((int) $params['id']);
        if ($c && (int) $c['provider_id'] === Auth::id()) {
            $updateData = ['status' => 'approved'];

            if ($c['method'] === 'video') {
                $meetingLink = trim((string) $this->input('meeting_link', ''));
                // Basic validation: don't accept Google Calendar share URLs as meeting links
                if (!empty($meetingLink)) {
                    $parsed = parse_url($meetingLink);
                    $host = strtolower($parsed['host'] ?? '');
                    if (str_contains($host, 'calendar.google.com') || str_contains($meetingLink, 'calendar.google.com')) {
                        Session::flash('error', 'Tautan yang dimasukkan tampak seperti link berbagi Google Calendar. Masukkan link meeting langsung (contoh: Zoom atau Google Meet).');
                        $this->redirect('/consultation/' . $params['id']);
                        return;
                    }

                    $updateData['meeting_link'] = $meetingLink;
                    // Try to parse meeting ID and pass (Zoom, Google Meet)
                    $meetingId = null; $meetingPass = null;
                    // Zoom join URL like /j/1234567890
                    if (preg_match('#/j/([0-9]+)#', $meetingLink, $m)) {
                        $meetingId = $m[1];
                    }
                    // Zoom pwd parameter
                    $parts = parse_url($meetingLink);
                    if (!empty($parts['query'])) {
                        parse_str($parts['query'], $q);
                        if (!empty($q['pwd'])) $meetingPass = $q['pwd'];
                        if (!$meetingId && !empty($q['confno'])) $meetingId = $q['confno'];
                    }
                    // Google Meet: meet.google.com/xxx-xxxx-xxx
                    if (!$meetingId && preg_match('#meet\.google\.com/([a-zA-Z0-9-]+)#', $meetingLink, $m2)) {
                        $meetingId = $m2[1];
                    }
                    if ($meetingId) $updateData['meeting_id'] = $meetingId;
                    if ($meetingPass) $updateData['meeting_pass'] = $meetingPass;
                } elseif (empty($c['meeting_link'])) {
                    $updateData['meeting_room_code'] = bin2hex(random_bytes(6));
                }

                if (!VideoSession::byConsultation($c['id'])) {
                    VideoSession::create(['consultation_id' => $c['id'], 'room_code' => bin2hex(random_bytes(6)), 'status' => 'waiting']);
                }
            }
            // Auto-generate Google Meet via Google Calendar jika belum ada link (untuk semua metode online).
            if (empty($updateData['meeting_link']) && empty($c['meeting_link']) && in_array($c['method'], ['video', 'voice', 'chat'], true)) {
                $patient = Patient::detailWithUser($c['patient_id']);
                $provider = User::find($c['provider_id']);
                if ($patient && $provider) {
                    try {
                        $autoLink = GoogleMeetService::createMeeting($c, $patient, $provider);
                        if ($autoLink) {
                            $updateData['meeting_link'] = $autoLink;
                        }
                    } catch (\Throwable $e) {
                        \Core\Logger::error('Auto-create Google Meet saat persetujuan gagal untuk konsultasi ' . $c['id'] . ': ' . $e->getMessage());
                    }
                }
            }
            // Only include columns that exist in the DB (some installs may not have the migration applied)
            $safe = \App\Models\Consultation::filterColumns($updateData);
            Consultation::update($c['id'], $safe);
            $patient = Patient::detailWithUser($c['patient_id']);
            if ($patient) {
                Notification::create(['user_id' => $this->patientUserId($c['patient_id']), 'title' => 'Booking Disetujui', 'message' => 'Booking konseling Anda telah disetujui.', 'type' => 'booking']);
            }
            $this->createCalendarEventForConsultation($c, $updateData);
            audit('approve', 'consultation', 'Menyetujui booking ID ' . $c['id']);
            // Prepare one-click WhatsApp prefill for provider to notify patient
            try {
                $patient = \App\Models\Patient::detailWithUser($c['patient_id']);
                $patientUser = $patient ?: null;
                $to = $patientUser['whatsapp_number'] ?? $patientUser['phone'] ?? null;
                if ($to) {
                    $normalized = $this->normalizePhone((string)$to);
                    if ($normalized) {
                        $msg = "Halo " . ($patientUser['name'] ?? 'Client') . ",%0APsikolog telah menyetujui sesi Anda.%0A";
                        if (!empty($updateData['meeting_link'])) $msg .= "Link: " . $updateData['meeting_link'] . "%0A";
                        if (!empty($updateData['meeting_id'])) $msg .= "ID: " . $updateData['meeting_id'] . "%0A";
                        if (!empty($updateData['meeting_pass'])) $msg .= "Pass: " . $updateData['meeting_pass'] . "%0A";
                        $msg .= "Tanggal: " . format_date($c['schedule_at'], 'd M Y H:i');
                        $waUrl = 'https://wa.me/' . $normalized . '?text=' . rawurlencode($msg);
                        \Core\Session::flash('wa_prefill', $waUrl);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }

            if ($c['payment_status'] === 'pending_verification') {
                Consultation::update($c['id'], [
                    'payment_status' => 'paid',
                    'payment_verified_by' => Auth::id(),
                    'payment_verified_at' => date('Y-m-d H:i:s'),
                ]);
                if (!empty($c['price_amount']) && !\App\Models\Invoice::forConsultation($c['id'])) {
                    \App\Models\Invoice::create([
                        'invoice_number' => \App\Models\Invoice::generateNumber(),
                        'consultation_id' => $c['id'],
                        'patient_id' => $c['patient_id'],
                        'amount' => $c['price_amount'],
                    ]);
                }
                PaymentTransaction::raw('UPDATE payment_transactions SET payment_status = ? WHERE reference_type = ? AND reference_id = ?', ['paid', 'consultation', $c['id']]);
                audit('update', 'payment', 'Pembayaran otomatis diverifikasi saat menyetujui booking ID ' . $c['id']);
                $paymentAutoVerified = true;
            }

            $approvalPatient = Patient::detailWithUser($c['patient_id']) ?: [];
            $approvalProvider = User::find((int) $c['provider_id']) ?: [];
            $approvalData = array_merge($c, ['payment_status' => !empty($paymentAutoVerified) ? 'paid' : ($c['payment_status'] ?? 'unpaid')]);
            \App\Services\CommunicationService::notifyConsultationApproval($approvalData, $approvalPatient, $approvalProvider);
        }
        Session::flash('success', !empty($paymentAutoVerified) ? 'Booking disetujui. Pembayaran juga otomatis diverifikasi dan invoice dibuat untuk client.' : 'Booking disetujui dan event kalender dibuat untuk client dan psikolog.');
        $this->redirect('/consultation/' . $params['id']);
    }

    private function createCalendarEventForConsultation(array $consultation, array $updateData = []): void
    {
        $patientUserId = $this->patientUserId($consultation['patient_id']);
        $providerUserId = (int) $consultation['provider_id'];
        $scheduleAt = $consultation['schedule_at'];
        $duration = (int) $consultation['duration_minutes'];
        $start = format_date($scheduleAt, 'd M Y H:i');
        $end = format_date(date('Y-m-d H:i:s', strtotime($scheduleAt) + $duration * 60), 'H:i');
        $message = 'Sesi konseling Anda dijadwalkan pada ' . $start . ' sampai ' . $end . '. Buka kalender untuk melihat detail sesi dan link meeting.';

        if ($patientUserId) {
            Notification::create([
                'user_id' => $patientUserId,
                'title' => 'Event Kalender Konseling Dibuat',
                'message' => $message,
                'type' => 'calendar',
                'url' => base_url('/calendar'),
            ]);
            CalendarSyncLog::create(['user_id' => $patientUserId, 'consultation_id' => $consultation['id'], 'provider' => 'internal']);
        }

        Notification::create([
            'user_id' => $providerUserId,
            'title' => 'Event Kalender Konseling Dibuat',
            'message' => $message,
            'type' => 'calendar',
            'url' => base_url('/calendar'),
        ]);
        CalendarSyncLog::create(['user_id' => $providerUserId, 'consultation_id' => $consultation['id'], 'provider' => 'internal']);
    }

    public function cancel(array $params): void
    {
        $this->requireCsrf();
        $c = Consultation::find((int) $params['id']);
        if ($c && (int) $c['patient_id'] === current_patient_id() && in_array($c['status'], ['pending', 'approved'], true)) {
            Consultation::update($c['id'], ['status' => 'cancelled']);
            audit('update', 'consultation', 'Client membatalkan booking ID ' . $c['id']);

            $consultation = Consultation::detail($c['id']);
            if ($consultation) {
                $hoursBefore = max(0, (strtotime($consultation['schedule_at']) - time()) / 3600);
                $refundPct = CancellationPolicy::refundPercentageFor($hoursBefore);
                $refundAmount = (int) round(($consultation['price_amount'] ?? 0) * $refundPct / 100);

                if (!CancellationRequest::forConsultation($consultation['id'])) {
                    CancellationRequest::create([
                        'consultation_id' => $consultation['id'],
                        'requested_by' => Auth::id(),
                        'reason' => 'Client membatalkan sesi ini.',
                        'hours_before_session' => (int) floor($hoursBefore),
                        'refund_eligible_percentage' => $refundPct,
                        'refund_amount' => $refundAmount,
                        'status' => 'pending',
                    ]);
                }
                audit('create', 'cancellation_request', 'Pembatalan dari tombol Batalkan booking ID ' . $consultation['id']);

                $patientUser = Patient::detailWithUser((int) $consultation['patient_id']) ?: [];
                $providerUser = User::find((int) $consultation['provider_id']) ?: [];
                \App\Services\CommunicationService::notifyConsultationCancellation(
                    $consultation,
                    $patientUser,
                    $providerUser,
                    (int) $consultation['provider_id'],
                    'Client membatalkan sesi ini melalui tombol Batalkan.',
                    $refundPct,
                    $refundAmount
                );
            }
        }
        Session::flash('success', 'Booking dibatalkan. Permintaan masuk ke Pembatalan & Refund psikolog dan notifikasi email telah dikirim.');
        $this->redirect('/consultation');
    }

    public function complete(array $params): void
    {
        $this->requireCsrf();
        $c = Consultation::find((int) $params['id']);
        if ($c && (int) $c['provider_id'] === Auth::id()) {
            Consultation::update($c['id'], ['status' => 'completed']);
            audit('update', 'consultation', 'Menandai sesi selesai, booking ID ' . $c['id']);
            Session::flash('complete_alert', 'Sesi berhasil ditandai selesai. Status booking telah diperbarui dan client dapat memberikan ulasan setelah sesi selesai.');
        }
        Session::flash('success', 'Sesi ditandai selesai.');
        $this->redirect('/consultation/' . $params['id']);
    }

    public function verifyPayment(array $params): void
    {
        $this->requireCsrf();
        $c = Consultation::find((int) $params['id']);
        if ($c && ((int) $c['provider_id'] === Auth::id() || in_array(Auth::roleName(), ['admin', 'super_admin'], true))) {
            Consultation::update($c['id'], [
                'payment_status' => 'paid',
                'payment_verified_by' => Auth::id(),
                'payment_verified_at' => date('Y-m-d H:i:s'),
            ]);

            if (!\App\Models\Invoice::forConsultation($c['id']) && !empty($c['price_amount'])) {
                \App\Models\Invoice::create([
                    'invoice_number' => \App\Models\Invoice::generateNumber(),
                    'consultation_id' => $c['id'],
                    'patient_id' => $c['patient_id'],
                    'amount' => $c['price_amount'],
                ]);
            }

            PaymentTransaction::raw('UPDATE payment_transactions SET payment_status = ? WHERE reference_type = ? AND reference_id = ?', ['paid', 'consultation', $c['id']]);
            audit('update', 'payment', 'Verifikasi pembayaran untuk booking ID ' . $c['id']);
            Session::flash('success', 'Pembayaran berhasil diverifikasi. Invoice otomatis dibuat untuk client.');
        }
        $this->redirect('/consultation/' . $params['id']);
    }

    public function rescheduleApprove(array $params): void
    {
        $this->requireCsrf();

        $reschedule = ConsultationReschedule::find((int) $params['id']);
        if (!$reschedule) {
            Session::flash('error', 'Permintaan reschedule tidak ditemukan.');
            $this->redirect('/consultation');
            return;
        }

        $consultation = Consultation::find($reschedule['original_consultation_id']);
        if (!$consultation || (int) $consultation['provider_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        if ($reschedule['status'] !== 'pending') {
            Session::flash('error', 'Permintaan reschedule sudah tidak dapat diproses lagi.');
            $this->redirect('/consultation/' . $consultation['id']);
            return;
        }

        if ((int)$reschedule['rescheduling_fee'] > 0 && $reschedule['payment_status'] !== 'paid') {
            Session::flash('error', 'Pembayaran penjadwalan ulang harus selesai sebelum menyetujui.');
            $this->redirect('/consultation/' . $consultation['id']);
            return;
        }

        $updatedConsultationId = ConsultationModificationService::approveReschedule($reschedule['id'], Auth::id());
        if ($updatedConsultationId) {
            $patient = Patient::find($consultation['patient_id']);
            Notification::create([
                'user_id' => $patient['user_id'] ?? $consultation['patient_id'],
                'title' => 'Penjadwalan ulang disetujui',
                'message' => 'Permintaan reschedule Anda telah disetujui. Jadwal dan invoice diperbarui di sesi yang sama.',
                'type' => 'reschedule',
                'url' => base_url('/consultation/' . $updatedConsultationId),
            ]);
            audit('approve', 'consultation_reschedule', 'Menyetujui reschedule ID ' . $reschedule['id']);
            Session::flash('success', 'Permintaan penjadwalan ulang disetujui dan jadwal diperbarui di sesi yang sama.');
            $this->redirect('/consultation/' . $updatedConsultationId);
        } else {
            Session::flash('error', 'Gagal menyetujui penjadwalan ulang. Pastikan pembayaran sudah selesai.');
            $this->redirect('/consultation/' . $consultation['id']);
        }
    }

    public function rescheduleReject(array $params): void
    {
        $this->requireCsrf();

        $reschedule = ConsultationReschedule::find((int) $params['id']);
        if (!$reschedule) {
            Session::flash('error', 'Permintaan reschedule tidak ditemukan.');
            $this->redirect('/consultation');
            return;
        }

        $consultation = Consultation::find($reschedule['original_consultation_id']);
        if (!$consultation || (int) $consultation['provider_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        ConsultationReschedule::update($reschedule['id'], ['status' => 'rejected']);
        $patient = Patient::find($consultation['patient_id']);
        Notification::create([
            'user_id' => $patient['user_id'] ?? $consultation['patient_id'],
            'title' => 'Reschedule ditolak',
            'message' => 'Permintaan penjadwalan ulang Anda telah ditolak. Silakan ajukan ulang jika diperlukan.',
            'type' => 'reschedule',
            'url' => base_url('/consultation/' . $consultation['id']),
        ]);
        audit('reject', 'consultation_reschedule', 'Menolak reschedule ID ' . $reschedule['id']);
        Session::flash('success', 'Permintaan penjadwalan ulang ditolak.');
        $this->redirect('/consultation/' . $consultation['id']);
    }

    private function normalizePhone(string $raw): ?string
    {
        $d = preg_replace('/[^0-9]/', '', $raw);
        if ($d === '') return null;
        // If starts with 0, assume local Indonesia '0' -> '62'
        if (strlen($d) >= 9 && $d[0] === '0') {
            return '62' . ltrim($d, '0');
        }
        // if already international without plus, return as-is
        return $d;
    }

    public function updateLink(array $params): void
    {
        $this->requireCsrf();
        $c = Consultation::find((int) $params['id']);
        if (!$c || (int)$c['provider_id'] !== Auth::id()) {
            Session::flash('error', 'Anda tidak memiliki izin untuk mengubah link ini.');
            $this->redirect('/consultation/' . $params['id']);
            return;
        }
        if ($c['method'] !== 'video') {
            Session::flash('error', 'Link meeting hanya tersedia untuk sesi online (video call).');
            $this->redirect('/consultation/' . $params['id']);
            return;
        }

        $meetingLink = trim((string) $this->input('meeting_link', ''));
        if (!empty($meetingLink)) {
            $parsed = parse_url($meetingLink);
            $host = strtolower($parsed['host'] ?? '');
            if (str_contains($host, 'calendar.google.com') || str_contains($meetingLink, 'calendar.google.com')) {
                Session::flash('error', 'Tautan yang dimasukkan tampak seperti link berbagi Google Calendar. Masukkan link meeting langsung (contoh: Zoom atau Google Meet).');
                $this->redirect('/consultation/' . $params['id']);
                return;
            }
            // parse meeting id/pass for storage
            $meetingId = null; $meetingPass = null;
            if (preg_match('#/j/([0-9]+)#', $meetingLink, $m)) { $meetingId = $m[1]; }
            if (!empty($parsed['query'])) { parse_str($parsed['query'], $q); if (!empty($q['pwd'])) $meetingPass = $q['pwd']; if (!$meetingId && !empty($q['confno'])) $meetingId = $q['confno']; }
            if (!$meetingId && preg_match('#meet\.google\.com/([a-zA-Z0-9-]+)#', $meetingLink, $m2)) { $meetingId = $m2[1]; }
        }

        $update = ['meeting_link' => $meetingLink];
        if (!empty($meetingId)) $update['meeting_id'] = $meetingId;
        if (!empty($meetingPass)) $update['meeting_pass'] = $meetingPass;
        // Filter update keys to existing columns to avoid SQL errors on older DBs
        $safeUpdate = \App\Models\Consultation::filterColumns($update);
        Consultation::update($c['id'], $safeUpdate);
        // Prepare one-click WhatsApp prefill for provider to notify patient about updated link
        try {
            $patient = \App\Models\Patient::detailWithUser($c['patient_id']);
            if (!empty($patient)) {
                $to = $patient['whatsapp_number'] ?? $patient['phone'] ?? null;
                if ($to) {
                    $msg = "Halo " . ($patient['name'] ?? 'Client') . ",%0APsikolog telah memperbarui link meeting untuk sesi Anda.%0A";
                    if (!empty($update['meeting_link'])) $msg .= "Link: " . $update['meeting_link'] . "%0A";
                    if (!empty($update['meeting_id'])) $msg .= "ID: " . $update['meeting_id'] . "%0A";
                    if (!empty($update['meeting_pass'])) $msg .= "Pass: " . $update['meeting_pass'] . "%0A";
                    $msg .= "Tanggal: " . format_date($c['schedule_at'], 'd M Y H:i');
                    $waUrl = CommunicationService::createWhatsAppChatUrl((string)$to, $msg);
                    \Core\Session::flash('wa_prefill', $waUrl);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        Session::flash('success', 'Link meeting diperbarui.');
        $this->redirect('/consultation/' . $params['id']);
    }

    public function extensionApprove(array $params): void
    {
        $this->requireCsrf();

        $extension = ConsultationExtension::find((int) $params['id']);
        if (!$extension) {
            Session::flash('error', 'Permintaan perpanjangan tidak ditemukan.');
            $this->redirect('/consultation');
            return;
        }

        $consultation = Consultation::find($extension['consultation_id']);
        if (!$consultation || (int) $consultation['provider_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        if ($extension['status'] !== 'pending') {
            Session::flash('error', 'Permintaan perpanjangan sudah tidak dapat diproses lagi.');
            $this->redirect('/consultation/' . $consultation['id']);
            return;
        }

        if ((int)$extension['extension_price'] > 0 && $extension['payment_status'] !== 'paid') {
            Session::flash('error', 'Pembayaran perpanjangan harus selesai sebelum menyetujui.');
            $this->redirect('/consultation/' . $consultation['id']);
            return;
        }

        $approved = ConsultationModificationService::approveExtension($extension['id'], Auth::id());
        if ($approved) {
            $patient = Patient::detailWithUser((int) $consultation['patient_id']) ?: [];
            $providerUser = User::find(Auth::id()) ?: [];
            CommunicationService::notifyExtensionApproved(
                Consultation::detail($consultation['id']),
                $patient,
                $providerUser,
                (int) ($extension['extension_minutes'] ?? 0),
                (int) ($extension['extension_price'] ?? 0)
            );
            audit('approve', 'consultation_extension', 'Menyetujui extension ID ' . $extension['id']);
            Session::flash('success', 'Permintaan perpanjangan disetujui. Durasi sesi telah diperbarui.');
            $this->redirect('/consultation/' . $consultation['id']);
        } else {
            Session::flash('error', 'Gagal menyetujui perpanjangan. Pastikan pembayaran sudah selesai.');
            $this->redirect('/consultation/' . $consultation['id']);
        }
    }

    public function extensionReject(array $params): void
    {
        $this->requireCsrf();

        $extension = ConsultationExtension::find((int) $params['id']);
        if (!$extension) {
            Session::flash('error', 'Permintaan perpanjangan tidak ditemukan.');
            $this->redirect('/consultation');
            return;
        }

        $consultation = Consultation::find($extension['consultation_id']);
        if (!$consultation || (int) $consultation['provider_id'] !== Auth::id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        ConsultationExtension::update($extension['id'], ['status' => 'rejected']);
        $patient = Patient::detailWithUser((int) $consultation['patient_id']) ?: [];
        $providerUser = User::find(Auth::id()) ?: [];
        CommunicationService::notifyExtensionRejected(
            Consultation::detail($consultation['id']),
            $patient,
            $providerUser
        );
        audit('reject', 'consultation_extension', 'Menolak extension ID ' . $extension['id']);
        Session::flash('success', 'Permintaan perpanjangan ditolak.');
        $this->redirect('/consultation/' . $consultation['id']);
    }

    public function addNote(array $params): void
    {
        \Core\Middleware::permission('consultation.manage_notes');
        $this->requireCsrf();
        $c = Consultation::find((int) $params['id']);
        if ($c && (int) $c['provider_id'] === Auth::id()) {
            ConsultationNote::create(['consultation_id' => $c['id'], 'author_id' => Auth::id(), 'note' => $this->input('note')]);
            audit('create', 'consultation_note', 'Menambahkan catatan konseling ID ' . $c['id']);
        }
        Session::flash('success', 'Catatan konseling disimpan.');
        $this->redirect('/consultation/' . $params['id']);
    }

    public function addFollowUp(array $params): void
    {
        \Core\Middleware::permission('consultation.manage_notes');
        $this->requireCsrf();
        $c = Consultation::find((int) $params['id']);
        if ($c && (int) $c['provider_id'] === Auth::id()) {
            $planText = $this->input('plan_text');
            $targetDate = $this->input('target_date') ?: null;
            FollowUpPlan::create([
                'consultation_id' => $c['id'],
                'created_by' => Auth::id(),
                'plan_text' => $planText,
                'target_date' => $targetDate,
                'status' => 'open',
            ]);

            $consultation = Consultation::detail($c['id']) ?: $c;
            $patientUser = Patient::detailWithUser((int) $consultation['patient_id']) ?: [];
            $providerUser = User::find((int) $consultation['provider_id']) ?: [];
            \App\Services\CommunicationService::notifyFollowUpPlan(
                $consultation,
                $patientUser,
                $providerUser,
                (string) $planText,
                $targetDate
            );

            audit('create', 'follow_up_plan', 'Menambahkan rencana tindak lanjut, booking ID ' . $c['id']);
        }
        Session::flash('success', 'Rencana tindak lanjut disimpan.');
        $this->redirect('/consultation/' . $params['id']);
    }

    /* --------------------- Telekonseling / Waiting Room --------------------- */

    public function room(array $params): void
    {
        $consultation = Consultation::detail((int) $params['id']);
        if (!$consultation || !$this->canAccess($consultation)) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }
        $videoSession = VideoSession::byConsultation($consultation['id']);
        if (!$videoSession) { http_response_code(404); require __DIR__.'/../Views/errors/404.php'; return; }
        $this->closeRoomForPatientNoShow($consultation, $videoSession);

        // If this is an online consultation and there's no meeting_link yet,
        // try to create a Google Meet automatically if integration is configured.
        try {
            if (in_array($consultation['method'] ?? '', ['video', 'voice', 'chat'], true) && empty($consultation['meeting_link'])) {
                $patient = Patient::detailWithUser($consultation['patient_id']);
                $provider = User::find($consultation['provider_id']);
                if ($patient && $provider) {
                    $link = GoogleMeetService::createMeeting($consultation, $patient, $provider);
                    if ($link) {
                        $safe = \App\Models\Consultation::filterColumns(['meeting_link' => $link]);
                        Consultation::update($consultation['id'], $safe);
                        // reload consultation so view shows the link
                        $consultation = Consultation::detail((int) $params['id']);
                        // create calendar event record if needed
                        $this->createCalendarEventForConsultation($consultation, ['meeting_link' => $link]);
                        CalendarSyncLog::create(['user_id' => $this->patientUserId($consultation['patient_id']), 'consultation_id' => $consultation['id'], 'provider' => 'google_meet']);
                        CalendarSyncLog::create(['user_id' => $consultation['provider_id'], 'consultation_id' => $consultation['id'], 'provider' => 'google_meet']);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Don't break the waiting room if meeting creation fails; log and continue
            \Core\Logger::error('Auto-create Google Meet failed for consultation ' . ($consultation['id'] ?? '?') . ': ' . $e->getMessage());
        }

        $videoSession = VideoSession::find($videoSession['id']);
        $attendance = SessionAttendance::forSession($videoSession['id']);
        $this->view('consultation.room', compact('consultation', 'videoSession', 'attendance'));
    }

    public function roomJoin(array $params): void
    {
        $videoSession = VideoSession::find((int) $params['id']);
        if (!$videoSession) { $this->redirect('/consultation'); }
        $consultation = Consultation::detail($videoSession['consultation_id']);
        if (!$consultation || !$this->canAccess($consultation)) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }

        $this->closeRoomForPatientNoShow($consultation, $videoSession);
        $videoSession = VideoSession::find($videoSession['id']);
        if ($videoSession['status'] === 'ended') {
            Session::flash('error', 'Ruang sesi sudah ditutup karena batas kehadiran 30 menit telah terlewati.');
            $this->redirect('/consultation/' . $videoSession['consultation_id'] . '/room');
        }
        if (!SessionAttendance::hasJoined($videoSession['id'], Auth::id())) {
            SessionAttendance::create(['video_session_id' => $videoSession['id'], 'user_id' => Auth::id(), 'joined_at' => date('Y-m-d H:i:s')]);
        }
        if ($videoSession['status'] === 'waiting') {
            VideoSession::update($videoSession['id'], ['status' => 'ongoing', 'started_at' => date('Y-m-d H:i:s')]);
        }
        $this->redirect('/consultation/' . $videoSession['consultation_id'] . '/room');
    }

    public function roomEnd(array $params): void
    {
        $this->requireCsrf();
        $videoSession = VideoSession::find((int) $params['id']);
        if (!$videoSession) { $this->redirect('/consultation'); }
        $consultation = Consultation::detail($videoSession['consultation_id']);
        if (!$consultation || !$this->canAccess($consultation) || !in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return;
        }
        VideoSession::update($videoSession['id'], [
            'status' => 'ended',
            'ended_at' => date('Y-m-d H:i:s'),
            'session_summary' => $this->input('session_summary'),
        ]);
        audit('update', 'video_session', 'Sesi telekonseling diakhiri, ID ' . $videoSession['id']);
        Session::flash('success', 'Sesi telekonseling diakhiri.');
        $this->redirect('/consultation/' . $videoSession['consultation_id']);
    }

    /* --------------------- Chat --------------------- */

    public function chatSend(array $params): void
    {
        $this->requireCsrf();
        $consultation = Consultation::find((int) $params['id']);
        if (!$consultation) { $this->redirect('/consultation'); }
        $detail = Consultation::detail($consultation['id']);
        if (!$this->canAccess($detail)) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }
        $receiverId = Auth::id() === (int) $consultation['provider_id'] ? $this->patientUserId($consultation['patient_id']) : (int) $consultation['provider_id'];
        ChatMessage::create([
            'consultation_id' => $consultation['id'],
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'message' => $this->input('message'),
        ]);
        $this->redirect('/consultation/' . $consultation['id']);
    }

    public function uploadDocument(array $params): void
    {
        \Core\Middleware::permission('consultation.manage_documents');
        $this->requireCsrf();
        $c = Consultation::find((int) $params['id']);
        if (!$c || (int) $c['provider_id'] !== Auth::id()) {
            Session::flash('error', 'Akses ditolak.');
            $this->redirect('/consultation/' . $params['id']);
            return;
        }

        $documentType = $this->input('document_type', 'lainnya');
        $description = $this->input('description', '');

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            Session::flash('error', 'Silakan pilih file dokumen.');
            $this->redirect('/consultation/' . $params['id']);
            return;
        }

        $file = $_FILES['document'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Gagal mengunggah dokumen. Silakan coba lagi.');
            $this->redirect('/consultation/' . $params['id']);
            return;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            Session::flash('error', 'Ukuran dokumen maksimal 10MB.');
            $this->redirect('/consultation/' . $params['id']);
            return;
        }

        $allowedMime = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMime[$mime])) {
            Session::flash('error', 'Format dokumen tidak didukung. Gunakan: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG.');
            $this->redirect('/consultation/' . $params['id']);
            return;
        }

        $dir = __DIR__ . '/../../public/assets/uploads/documents';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $filename = 'doc-' . (int) $params['id'] . '-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            Session::flash('error', 'Gagal menyimpan dokumen ke server.');
            $this->redirect('/consultation/' . $params['id']);
            return;
        }

        $filePath = 'assets/uploads/documents/' . $filename;
        ConsultationDocument::create([
            'consultation_id' => (int) $params['id'],
            'document_type' => $documentType,
            'file_name' => $file['name'],
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'mime_type' => $mime,
            'uploaded_by' => Auth::id(),
            'description' => $description ?: null,
            'is_visible_to_patient' => 1,
        ]);

        audit('create', 'consultation_document', 'Mengunggah dokumen konseling ID ' . $params['id'] . ', tipe: ' . $documentType);
        Session::flash('success', 'Dokumen berhasil diunggah dan siap dilihat client.');
        $this->redirect('/consultation/' . $params['id']);
    }

    public function downloadDocument(array $params): void
    {
        $doc = ConsultationDocument::findWithUploader((int) $params['doc_id']);
        if (!$doc) {
            http_response_code(404);
            echo 'Dokumen tidak ditemukan.';
            return;
        }

        $consultation = Consultation::find($doc['consultation_id']);
        if (!$consultation || !$this->canAccess($consultation)) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        if ($doc['is_visible_to_patient'] == 0 && Auth::roleName() === 'pasien') {
            http_response_code(403);
            echo 'Dokumen ini belum tersedia untuk dilihat.';
            return;
        }

        $filePath = __DIR__ . '/../../public/' . $doc['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'File tidak ditemukan di server.';
            return;
        }

        header('Content-Type: ' . $doc['mime_type']);
        header('Content-Disposition: attachment; filename="' . $doc['file_name'] . '"');
        header('Content-Length: ' . $doc['file_size']);
        header('Pragma: no-cache');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        readfile($filePath);
        exit;
    }

    public function deleteDocument(array $params): void
    {
        \Core\Middleware::permission('consultation.manage_documents');
        $this->requireCsrf();
        $doc = ConsultationDocument::find((int) $params['doc_id']);
        if (!$doc) {
            Session::flash('error', 'Dokumen tidak ditemukan.');
            $this->redirect('/consultation');
            return;
        }

        $consultation = Consultation::find($doc['consultation_id']);
        if (!$consultation || (int) $consultation['provider_id'] !== Auth::id()) {
            Session::flash('error', 'Akses ditolak.');
            $this->redirect('/consultation');
            return;
        }

        $filePath = __DIR__ . '/../../public/' . $doc['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        ConsultationDocument::delete($doc['id']);
        audit('delete', 'consultation_document', 'Menghapus dokumen ID ' . $doc['id']);
        Session::flash('success', 'Dokumen berhasil dihapus.');
        $this->redirect('/consultation/' . $consultation['id']);
    }

    private function canAccess(array $consultation): bool
    {
        if (Auth::roleName() === 'pasien') return (int) $consultation['patient_id'] === current_patient_id();
        if ((int) $consultation['provider_id'] === Auth::id()) return true;
        return in_array(Auth::roleName(), ['admin', 'super_admin'], true);
    }

    private function patientUserId(int $patientId): ?int
    {
        $p = \App\Models\Patient::find($patientId);
        return $p['user_id'] ?? null;
    }

    private function closeRoomForPatientNoShow(array $consultation, array $videoSession): void
    {
        if ($videoSession['status'] === 'ended' || $consultation['method'] !== 'video') return;
        $usesInternalRoom = empty($consultation['meeting_link']) || str_contains((string) ($consultation['meeting_link'] ?? ''), '/room');
        if (!$usesInternalRoom) return;
        $deadline = strtotime($consultation['schedule_at']) + 30 * 60;
        $patientUserId = $this->patientUserId((int) $consultation['patient_id']);
        if (time() < $deadline || !$patientUserId || SessionAttendance::hasJoined((int) $videoSession['id'], (int) $patientUserId)) return;

        VideoSession::update($videoSession['id'], [
            'status' => 'ended',
            'ended_at' => date('Y-m-d H:i:s'),
            'session_summary' => 'Sesi ditutup otomatis: client tidak hadir dalam 30 menit sejak jadwal dimulai.',
        ]);
        Consultation::update($consultation['id'], ['status' => 'no_show']);
        audit('update', 'consultation', 'Menandai no-show booking ID ' . $consultation['id'] . ' karena client tidak hadir selama 30 menit.');
    }

    /* --------------------- RESCHEDULE MANAGEMENT --------------------- */

    public function rescheduleRequest(array $params): void
    {
        $consultation = Consultation::detail((int) $params['id']);
        if (!$consultation || (int) $consultation['patient_id'] !== current_patient_id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        if (!in_array($consultation['status'], ['pending', 'approved'], true)) {
            Session::flash('error', 'Hanya konsultasi yang pending atau approved yang bisa dijadwalkan ulang.');
            $this->redirect('/consultation/' . $consultation['id']);
            return;
        }

        $reschedule_fee = ConsultationModificationService::calculateRescheduleFee($consultation['id']);
        $reschedule_fee_percentage = ConsultationModificationService::rescheduleFeePercentage();
        $hours_until_session = ConsultationModificationService::hoursUntilConsultation($consultation);
        $availableSlots = Schedule::getAvailableSlots($consultation['provider_id'], 14, $consultation['method'] === 'tatap_muka' ? 'offline' : 'online');

        $this->view('consultation.reschedule_request', compact('consultation', 'reschedule_fee', 'reschedule_fee_percentage', 'hours_until_session', 'availableSlots'));
    }

    public function rescheduleStore(array $params): void
    {
        $this->requireCsrf();
        $consultation = Consultation::find((int) $params['id']);
        
        if (!$consultation || (int) $consultation['patient_id'] !== current_patient_id()) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        $newScheduleAt = $this->input('new_schedule_at');
        $reason = $this->input('reason', '');

        $reschedule = ConsultationModificationService::requestReschedule(
            $consultation['id'],
            $newScheduleAt,
            $reason,
            Auth::id()
        );

        if ($reschedule) {
            $isFreeReschedule = (int) ($reschedule['rescheduling_fee'] ?? 0) === 0;
            if ($isFreeReschedule) {
                ConsultationReschedule::update($reschedule['id'], [
                    'payment_status' => 'paid',
                    'paid_at' => date('Y-m-d H:i:s'),
                ]);
            }
            Notification::create([
                'user_id' => $consultation['provider_id'],
                'title' => 'Permintaan Penjadwalan Ulang',
                'message' => $isFreeReschedule
                    ? 'Ada permintaan penjadwalan ulang konsultasi tanpa biaya tambahan.'
                    : 'Ada permintaan penjadwalan ulang konsultasi dengan biaya tambahan Rp100.000.',
                'type' => 'reschedule',
                'url' => base_url('/consultation/' . $consultation['id']),
            ]);

            audit('create', 'consultation_reschedule', 'Client membuat permintaan penjadwalan ulang ID ' . $reschedule['id']);
            Session::flash('success', $isFreeReschedule
                ? 'Permintaan penjadwalan ulang terkirim tanpa biaya. Menunggu persetujuan psikolog.'
                : 'Permintaan penjadwalan ulang terkirim. Silakan lanjutkan ke pembayaran Rp100.000.');
            $this->redirect($isFreeReschedule
                ? '/consultation/' . $consultation['id']
                : '/payment/reschedule/' . $reschedule['id']);
        } else {
            Session::flash('error', 'Gagal membuat permintaan penjadwalan ulang.');
            $this->redirect('/consultation/' . $consultation['id']);
        }
    }

    /* --------------------- EXTEND MANAGEMENT --------------------- */

    public function extendRequest(array $params): void
    {
        $consultation = Consultation::detail((int) $params['id']);
        if (!$consultation || (int) $consultation['provider_id'] !== Auth::id() || !in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        if (!in_array($consultation['status'], ['pending', 'approved'], true)) {
            Session::flash('error', 'Hanya konsultasi yang pending atau approved yang bisa diperpanjang.');
            $this->redirect('/consultation/' . $consultation['id']);
            return;
        }

        // Calculate price per minute for extension
        $psy = Psychologist::byUserId($consultation['provider_id']);
        $psi = Psychiatrist::byUserId($consultation['provider_id']);
        $tier = ($psy['tier'] ?? null) ?: ($psi['tier'] ?? 'associate');
        $basePrice = (int)($consultation['price_amount'] ?? 0);
        $currentDuration = (int)$consultation['duration_minutes'];
        $price_per_minute = $currentDuration > 0 ? $basePrice / $currentDuration : 0;
        $extension_price = ConsultationModificationService::calculateExtensionPrice($consultation['id'], 15);

        $this->view('consultation.extend_request', compact('consultation', 'price_per_minute', 'extension_price'));
    }

    public function extendStore(array $params): void
    {
        $this->requireCsrf();
        $consultation = Consultation::find((int) $params['id']);
        
        if (!$consultation || (int) $consultation['provider_id'] !== Auth::id() || !in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            http_response_code(403);
            require __DIR__.'/../Views/errors/403.php';
            return;
        }

        $extensionMinutes = (int)$this->input('extension_minutes', 15);
        $reason = $this->input('reason', '');

        if ($extensionMinutes < 15 || $extensionMinutes > 180) {
            Session::flash('error', 'Durasi perpanjangan harus antara 15-180 menit.');
            $this->redirect('/consultation/' . $consultation['id'] . '/extend');
            return;
        }

        $extension = ConsultationModificationService::requestExtension(
            $consultation['id'],
            $extensionMinutes,
            $reason,
            Auth::id()
        );

        if ($extension) {
            $patient = Patient::detailWithUser((int) $consultation['patient_id']) ?: [];
            $providerUser = User::find((int) $consultation['provider_id']) ?: [];
            CommunicationService::notifyExtensionRequest(
                Consultation::detail($consultation['id']),
                $patient,
                $providerUser,
                $extensionMinutes,
                (int) ($extension['extension_price'] ?? 0),
                (int) $extension['id'],
                $reason
            );

            audit('create', 'consultation_extension', 'Psikolog membuat permintaan perpanjangan ID ' . $extension['id']);
            Session::flash('success', 'Permintaan perpanjangan terkirim ke client. Client akan menerima notifikasi email untuk menyetujui dan menyelesaikan pembayaran tambahan.');
            $this->redirect('/consultation/' . $consultation['id']);
        } else {
            Session::flash('error', 'Gagal membuat permintaan perpanjangan.');
            $this->redirect('/consultation/' . $consultation['id'] . '/extend');
        }
    }

}
