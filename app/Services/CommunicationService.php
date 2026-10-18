<?php
namespace App\Services;

use App\Models\IntegrationConfig;
use App\Models\Notification;
use App\Models\Setting;
use Core\Logger;

class CommunicationService
{
    public static function notifyConsultationBooking(array $consultation, array $patientUser, array $providerUser, array $providerProfile = []): void
    {
        $patientName = $patientUser['name'] ?? 'Pasien';
        $providerName = $providerUser['name'] ?? 'Psikolog';

        $patientNumber = self::normalizeWhatsappNumber($patientUser['phone'] ?? $patientUser['whatsapp_number'] ?? null);
        $providerNumber = self::normalizeWhatsappNumber($providerProfile['whatsapp_number'] ?? $providerUser['whatsapp_number'] ?? $providerUser['phone'] ?? null);

        $summary = self::buildBookingSummary($consultation, $patientUser, $providerUser);
        $subjectPatient = "Booking Konseling Anda dengan {$providerName}";
        $subjectProvider = "Permintaan Booking Konseling Baru dari {$patientName}";

        $proofUrl = !empty($consultation['payment_proof_path']) ? self::resolvePublicUrl($consultation['payment_proof_path']) : null;

        $messagePatient = "Halo {$patientName},\n\nBooking konseling Anda telah berhasil dibuat. Berikut detailnya:\n\n{$summary}";
        $messageProvider = "Halo {$providerName},\n\nAnda menerima permintaan booking konseling baru. Berikut detailnya:\n\n{$summary}";

        if ($proofUrl) {
            $messagePatient .= "\n\nBukti Transfer: {$proofUrl}";
            $messageProvider .= "\n\nBukti Transfer: {$proofUrl}";
        }

        $messagePatient .= "\n\nSilakan tunggu konfirmasi dari psikolog. Terima kasih.";
        $messageProvider .= "\n\nSilakan masuk ke dashboard untuk meninjau dan menyetujui booking ini.";

        $patientWhatsAppUrl = null;
        $providerWhatsAppUrl = null;
        if ($providerNumber) {
            $patientWhatsAppUrl = self::createWhatsAppChatUrl($providerNumber, "Halo {$providerName},\n\nBooking konseling Anda telah dibuat untuk klien {$patientName} pada {$consultation['schedule_at']}.");
        }
        if ($patientNumber) {
            $providerWhatsAppUrl = self::createWhatsAppChatUrl($patientNumber, "Halo {$patientName},\n\nAda permintaan booking konseling baru dari Anda dengan {$providerName} pada {$consultation['schedule_at']}.");
        }

        $htmlPatient = self::buildBookingHtml($consultation, $patientUser, $providerUser, true, $patientWhatsAppUrl, $providerName, $proofUrl);
        $htmlProvider = self::buildBookingHtml($consultation, $patientUser, $providerUser, false, $providerWhatsAppUrl, $patientName, $proofUrl);

        $attachments = [];
        if (!empty($consultation['payment_proof_path']) && ($storagePath = self::resolveStoragePath($consultation['payment_proof_path']))) {
            $attachments[] = [
                'path' => $storagePath,
                'name' => basename($storagePath),
                'mime' => self::mimeTypeFromPath($storagePath),
            ];
        }

        $plainPatient = $messagePatient . ($patientWhatsAppUrl ? "\n\nKirim via WhatsApp: {$patientWhatsAppUrl}" : '');
        $plainProvider = $messageProvider . ($providerWhatsAppUrl ? "\n\nKirim via WhatsApp: {$providerWhatsAppUrl}" : '');

        if (!empty($patientUser['email'])) {
            self::sendEmail($patientUser['email'], $patientName, $subjectPatient, $htmlPatient, $plainPatient, $attachments);
        }

        if (!empty($providerUser['email'])) {
            self::sendEmail($providerUser['email'], $providerName, $subjectProvider, $htmlProvider, $plainProvider, $attachments);
        }

        if ($patientNumber) {
            if (self::isWhatsAppApiEnabled()) {
                if (!empty($attachments)) {
                    self::sendWhatsAppMedia($patientNumber, $messagePatient, self::resolvePublicUrl($consultation['payment_proof_path']), basename($consultation['payment_proof_path']), $attachments[0]['mime']);
                } else {
                    self::sendWhatsApp($patientNumber, $messagePatient);
                }
            } else {
                Logger::info('WhatsApp API tidak aktif. Manual link untuk client: ' . ($patientWhatsAppUrl ?? 'tidak tersedia'));
            }
        }

        if ($providerNumber) {
            if (self::isWhatsAppApiEnabled()) {
                if (!empty($attachments)) {
                    self::sendWhatsAppMedia($providerNumber, $messageProvider, self::resolvePublicUrl($consultation['payment_proof_path']), basename($consultation['payment_proof_path']), $attachments[0]['mime']);
                } else {
                    self::sendWhatsApp($providerNumber, $messageProvider);
                }
            } else {
                Logger::info('WhatsApp API tidak aktif. Manual link untuk provider: ' . ($providerWhatsAppUrl ?? 'tidak tersedia'));
            }
        }
    }

    public static function notifyConsultationApproval(array $consultation, array $patientUser, array $providerUser): void
    {
        $patientName = $patientUser['name'] ?? 'Pasien';
        $providerName = $providerUser['name'] ?? 'Psikolog';
        $patientNumber = self::normalizeWhatsappNumber($patientUser['phone'] ?? $patientUser['whatsapp_number'] ?? null);

        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $method = self::mapMethod($consultation['method'] ?? 'video');
        $meetingLink = $consultation['meeting_link'] ?? '';
        $paymentStatus = ucfirst(str_replace('_', ' ', $consultation['payment_status'] ?? 'unpaid'));
        $isPaid = ($consultation['payment_status'] ?? '') === 'paid';

        $subject = "Booking Konseling Disetujui oleh {$providerName}";

        $summary = self::buildBookingSummary($consultation, $patientUser, $providerUser);

        $plain = "Halo {$patientName},\n\nBooking konseling Anda dengan {$providerName} telah disetujui!\n\n";
        $plain .= "Jadwal: {$schedule}\nMetode: {$method}\nDurasi: " . (int) ($consultation['duration_minutes'] ?? 0) . " menit\nStatus Pembayaran: {$paymentStatus}\n";
        if ($meetingLink) {
            $plain .= "Link Meeting: {$meetingLink}\n";
        }
        $plain .= "\n{$summary}\n";
        $plain .= "Silakan cek detail booking di aplikasi untuk informasi lebih lanjut. Terima kasih.";

        $html = self::buildApprovalHtml($consultation, $patientUser, $providerUser);

        if (!empty($patientUser['email'])) {
            self::sendEmail($patientUser['email'], $patientName, $subject, $html, $plain);
        }

        if ($patientNumber && self::isWhatsAppApiEnabled()) {
            $waMessage = "Halo {$patientName}, Booking konseling Anda dengan {$providerName} telah disetujui!\n\n";
            $waMessage .= "Jadwal: {$schedule}\nMetode: {$method}\n";
            if ($meetingLink) {
                $waMessage .= "Link Meeting: {$meetingLink}\n";
            }
            $waMessage .= "\nSilakan cek detail booking di aplikasi.";
            self::sendWhatsApp($patientNumber, $waMessage);
        }
    }

    public static function notifyFollowUpPlan(array $consultation, array $patientUser, array $providerUser, string $planText = '', ?string $targetDate = null): void
    {
        $patientName = $patientUser['name'] ?? 'Pasien';
        $providerName = $providerUser['name'] ?? 'Psikolog';
        $patientNumber = self::normalizeWhatsappNumber($patientUser['phone'] ?? $patientUser['whatsapp_number'] ?? null);

        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $targetLabel = $targetDate ? date('d M Y', strtotime($targetDate)) : '-';
        $planLabel = trim($planText) !== '' ? trim($planText) : '-';

        $subject = "Rencana Tindak Lanjut dari {$providerName}";

        $plain = "Halo {$patientName},\n\n"
            . "Psikolog Anda, {$providerName}, telah menyimpan rencana tindak lanjut untuk sesi konseling Anda (" . $schedule . ").\n\n"
            . "Rencana Tindak Lanjut:\n" . $planLabel . "\n"
            . "Target Selesai: " . $targetLabel . "\n\n"
            . "Silakan cek detail lengkapnya di aplikasi. Terima kasih.";

        $html = self::buildFollowUpHtml($patientName, $providerName, $schedule, $planLabel, $targetLabel);

        if (!empty($patientUser['email'])) {
            self::sendEmail($patientUser['email'], $patientName, $subject, $html, $plain);
        }

        if ($patientNumber && self::isWhatsAppApiEnabled()) {
            $waMessage = "Halo {$patientName}, Psikolog Anda ({$providerName}) telah menyimpan rencana tindak lanjut sesi konseling.\n\n"
                . "Rencana:\n" . $planLabel . "\nTarget: " . $targetLabel . "\n\nSilakan cek aplikasi untuk detail lengkap.";
            self::sendWhatsApp($patientNumber, $waMessage);
        }
    }

    public static function notifyExtensionRequest(
        array $consultation,
        array $patientUser,
        array $providerUser,
        int $extensionMinutes,
        int $extensionPrice,
        int $extensionId,
        string $reason = ''
    ): void {
        $patientName = $patientUser['name'] ?? 'Pasien';
        $providerName = $providerUser['name'] ?? 'Psikolog';
        $patientNumber = self::normalizeWhatsappNumber($patientUser['phone'] ?? $patientUser['whatsapp_number'] ?? null);
        $patientUserId = (int) ($patientUser['user_id'] ?? 0);

        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $payUrl = base_url('/payment/extension/' . $extensionId);
        $reasonLine = trim($reason) !== '' ? "\nAlasan: " . trim($reason) : '';

        $subject = 'Permintaan Perpanjangan Durasi – Nurture For Growth';

        $plain = "Halo {$patientName},\n\n"
            . "Psikolog Anda, {$providerName}, mengajukan perpanjangan durasi untuk sesi konseling Anda pada {$schedule}.\n\n"
            . "Durasi tambahan: {$extensionMinutes} menit\n"
            . "Biaya tambahan: Rp" . number_format($extensionPrice, 0, ',', '.') . "\n"
            . $reasonLine . "\n\n"
            . "Perpanjangan hanya berlaku setelah Anda menyelesaikan pembayaran tambahan.\n"
            . "Silakan selesaikan pembayaran Anda di: {$payUrl}\n\n"
            . "Terima kasih.";

        $html = self::buildExtensionRequestHtml(
            $patientName,
            $providerName,
            $schedule,
            $extensionMinutes,
            $extensionPrice,
            $payUrl,
            $reasonLine
        );

        if (!empty($patientUser['email'])) {
            self::sendEmail($patientUser['email'], $patientName, $subject, $html, $plain);
        }

        if ($patientNumber && self::isWhatsAppApiEnabled()) {
            $waMessage = "Halo {$patientName}, Psikolog Anda ({$providerName}) mengajukan perpanjangan sesi {$extensionMinutes} menit "
                . "(Rp" . number_format($extensionPrice, 0, ',', '.') . ").\n"
                . "Silakan selesaikan pembayaran tambahan di aplikasi agar perpanjangan berlaku.";
            self::sendWhatsApp($patientNumber, $waMessage);
        }

        if ($patientUserId > 0) {
            Notification::create([
                'user_id' => $patientUserId,
                'title' => 'Permintaan Perpanjangan Durasi',
                'message' => 'Psikolog Anda mengajukan perpanjangan durasi sesi sebesar ' . $extensionMinutes . ' menit. Silakan selesaikan pembayaran tambahan.',
                'type' => 'extension',
                'url' => base_url('/payment/extension/' . $extensionId),
            ]);
        }
    }

    public static function notifyExtensionPaymentVerified(
        array $consultation,
        array $patientUser,
        array $providerUser,
        int $extensionId
    ): void {
        $patientName = $patientUser['name'] ?? 'Pasien';
        $providerName = $providerUser['name'] ?? 'Psikolog';
        $patientNumber = self::normalizeWhatsappNumber($patientUser['phone'] ?? $patientUser['whatsapp_number'] ?? null);
        $patientUserId = (int) ($patientUser['user_id'] ?? 0);

        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $subject = 'Pembayaran Perpanjangan Diterima – Nurture For Growth';

        $plain = "Halo {$patientName},\n\n"
            . "Pembayaran perpanjangan durasi untuk sesi konseling Anda bersama {$providerName} pada {$schedule} telah kami terima.\n\n"
            . "Psikolog Anda akan segera menyetujui perpanjangan dan durasi sesi akan diperbarui.\n\n"
            . "Terima kasih.";

        $html = self::buildExtensionPaymentVerifiedHtml($patientName, $providerName, $schedule);

        if (!empty($patientUser['email'])) {
            self::sendEmail($patientUser['email'], $patientName, $subject, $html, $plain);
        }

        if ($patientNumber && self::isWhatsAppApiEnabled()) {
            $waMessage = "Halo {$patientName}, pembayaran perpanjangan sesi Anda bersama {$providerName} telah diterima. Psikolog akan segera menyetujui perpanjangan.";
            self::sendWhatsApp($patientNumber, $waMessage);
        }

        if ($patientUserId > 0) {
            Notification::create([
                'user_id' => $patientUserId,
                'title' => 'Pembayaran Perpanjangan Diterima',
                'message' => 'Pembayaran perpanjangan sesi Anda telah diterima. Psikolog akan segera menyetujui perpanjangan.',
                'type' => 'extension',
                'url' => base_url('/consultation/' . $consultation['id']),
            ]);
        }
    }

    public static function notifyExtensionApproved(
        array $consultation,
        array $patientUser,
        array $providerUser,
        int $extensionMinutes,
        int $extensionPrice
    ): void {
        $patientName = $patientUser['name'] ?? 'Pasien';
        $providerName = $providerUser['name'] ?? 'Psikolog';
        $patientNumber = self::normalizeWhatsappNumber($patientUser['phone'] ?? $patientUser['whatsapp_number'] ?? null);
        $patientUserId = (int) ($patientUser['user_id'] ?? 0);

        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $subject = 'Perpanjangan Disetujui – Nurture For Growth';

        $plain = "Halo {$patientName},\n\n"
            . "Permintaan perpanjangan sesi konseling Anda bersama {$providerName} untuk sesi pada {$schedule} telah disetujui.\n\n"
            . "Durasi sesi bertambah {$extensionMinutes} menit"
            . ($extensionPrice > 0 ? " dengan biaya tambahan Rp" . number_format($extensionPrice, 0, ',', '.') : '')
            . ".\n\nTerima kasih.";

        $html = self::buildExtensionApprovedHtml($patientName, $providerName, $schedule, $extensionMinutes, $extensionPrice);

        if (!empty($patientUser['email'])) {
            self::sendEmail($patientUser['email'], $patientName, $subject, $html, $plain);
        }

        if ($patientNumber && self::isWhatsAppApiEnabled()) {
            $waMessage = "Halo {$patientName}, perpanjangan sesi Anda bersama {$providerName} telah disetujui (+{$extensionMinutes} menit).";
            self::sendWhatsApp($patientNumber, $waMessage);
        }

        if ($patientUserId > 0) {
            Notification::create([
                'user_id' => $patientUserId,
                'title' => 'Perpanjangan Disetujui',
                'message' => 'Permintaan perpanjangan sesi Anda telah disetujui. Durasi sesi bertambah ' . $extensionMinutes . ' menit.',
                'type' => 'extension',
                'url' => base_url('/consultation/' . $consultation['id']),
            ]);
        }
    }

    public static function notifyExtensionRejected(
        array $consultation,
        array $patientUser,
        array $providerUser
    ): void {
        $patientName = $patientUser['name'] ?? 'Pasien';
        $providerName = $providerUser['name'] ?? 'Psikolog';
        $patientNumber = self::normalizeWhatsappNumber($patientUser['phone'] ?? $patientUser['whatsapp_number'] ?? null);
        $patientUserId = (int) ($patientUser['user_id'] ?? 0);

        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $subject = 'Perpanjangan Ditolak – Nurture For Growth';

        $plain = "Halo {$patientName},\n\n"
            . "Permintaan perpanjangan sesi konseling Anda bersama {$providerName} untuk sesi pada {$schedule} telah ditolak.\n\n"
            . "Sesi tetap berjalan sesuai durasi semula. Terima kasih.";

        $html = self::buildExtensionRejectedHtml($patientName, $providerName, $schedule);

        if (!empty($patientUser['email'])) {
            self::sendEmail($patientUser['email'], $patientName, $subject, $html, $plain);
        }

        if ($patientNumber && self::isWhatsAppApiEnabled()) {
            $waMessage = "Halo {$patientName}, permintaan perpanjangan sesi Anda bersama {$providerName} telah ditolak.";
            self::sendWhatsApp($patientNumber, $waMessage);
        }

        if ($patientUserId > 0) {
            Notification::create([
                'user_id' => $patientUserId,
                'title' => 'Perpanjangan Ditolak',
                'message' => 'Permintaan perpanjangan sesi Anda telah ditolak.',
                'type' => 'extension',
                'url' => base_url('/consultation/' . $consultation['id']),
            ]);
        }
    }

    public static function notifyConsultationCancellation(
        array $consultation,
        array $patientUser,
        array $providerUser,
        int $notifyUserId,
        string $reason = '',
        int $refundPercentage = 0,
        int $refundAmount = 0
    ): void
    {
        $patientUserId = (int) ($patientUser['user_id'] ?? 0);
        $providerUserId = (int) ($providerUser['id'] ?? 0);
        $toPatient = $notifyUserId === $patientUserId;
        $recipientUser = $toPatient ? $patientUser : $providerUser;
        $otherUser = $toPatient ? $providerUser : $patientUser;

        $recipientName = $recipientUser['name'] ?? 'Pengguna';
        $otherName = $otherUser['name'] ?? 'Psikolog';
        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $summary = self::buildBookingSummary($consultation, $patientUser, $providerUser);

        $refundLine = '';
        if ($refundAmount > 0) {
            $refundLine = "\n\nEstimasi refund: {$refundPercentage}% dari biaya sesi (Rp" . number_format($refundAmount, 0, ',', '.') . "). Tim kami akan meninjau kelayakan refund dalam 1x24 jam.";
        }
        if (trim($reason) !== '') {
            $refundLine .= "\nAlasan pembatalan: " . trim($reason);
        }

        $message = "Halo {$recipientName},\n\n"
            . ($toPatient
                ? "Sesi konseling Anda bersama {$otherName} pada {$schedule} telah dibatalkan. Berikut detail sesi:\n\n{$summary}"
                : "Sesi konseling bersama client {$otherName} pada {$schedule} telah dibatalkan. Berikut detail sesi:\n\n{$summary}")
            . "{$refundLine}\n\nJika ada pertanyaan, silakan hubungi tim Nurture For Growth.";

        $subject = 'Sesi Konseling Dibatalkan – Nurture For Growth';
        $html = self::buildCancellationHtml($recipientName, $summary, $refundLine, $toPatient, $otherName, $schedule);

        if (!empty($recipientUser['email'])) {
            self::sendEmail($recipientUser['email'], $recipientName, $subject, $html, $message);
        }

        $number = self::normalizeWhatsappNumber($recipientUser['phone'] ?? $recipientUser['whatsapp_number'] ?? null);
        if ($number) {
            if (self::isWhatsAppApiEnabled()) {
                self::sendWhatsApp($number, $message);
            } else {
                Logger::info('WhatsApp API tidak aktif. Notifikasi pembatalan untuk ' . $recipientName . ' dikirim via email saja.');
            }
        }

        $recipientUserId = $toPatient ? $patientUserId : $providerUserId;
        if ($recipientUserId > 0) {
            Notification::create([
                'user_id' => $recipientUserId,
                'title' => 'Sesi Konseling Dibatalkan',
                'message' => $toPatient
                    ? "Sesi konseling Anda bersama {$otherName} pada {$schedule} telah dibatalkan."
                    : "Sesi konseling bersama client {$otherName} pada {$schedule} telah dibatalkan.",
                'type' => 'cancellation',
                'url' => base_url('/consultation/' . $consultation['id']),
            ]);
        }
    }

    public static function notifyCancellationRefundDecision(
        array $consultation,
        array $patientUser,
        array $providerUser,
        int $notifyUserId,
        string $decision,
        string $adminNote = ''
    ): void
    {
        $patientUserId = (int) ($patientUser['user_id'] ?? 0);
        $providerUserId = (int) ($providerUser['id'] ?? 0);
        $toPatient = $notifyUserId === $patientUserId;

        $recipientName = $toPatient ? ($patientUser['name'] ?? 'Pasien') : ($providerUser['name'] ?? 'Psikolog');
        $summary = self::buildBookingSummary($consultation, $patientUser, $providerUser);

        $label = $decision === 'approved' ? 'DISETUJUI' : 'DITOLAK';
        $message = "Halo {$recipientName},\n\nPengajuan pembatalan sesi konseling Anda telah {$label}. Berikut detail sesi:\n\n{$summary}";
        if ($decision === 'approved') {
            $message .= "\n\nRefund yang disetujui telah diinformasikan oleh tim Nurture For Growth. Dana akan dikembalikan sesuai metode pembayaran.";
        }
        if (trim($adminNote) !== '') {
            $message .= "\n\nCatatan admin: " . trim($adminNote);
        }
        $message .= "\n\nJika ada pertanyaan, silakan hubungi tim Nurture For Growth.";

        $subject = $decision === 'approved' ? 'Refund Sesi Konseling Disetujui' : 'Pengajuan Pembatalan Ditolak';

        $html = '<div style="font-family:Arial, sans-serif; color:#333;">'
            . '<h2 style="color:#2f855a;">' . self::escapeHtml($subject) . '</h2>'
            . '<p>Pengajuan pembatalan sesi konseling Anda telah <strong>' . self::escapeHtml($label) . '</strong>.</p>'
            . '<p>Berikut detail sesi:</p>'
            . '<pre style="font-family:Consolas,monospace;font-size:13px;background:#f6f9f7;padding:12px;border-radius:8px;">' . self::escapeHtml($summary) . '</pre>'
            . (trim($adminNote) !== '' ? '<p>Catatan admin: ' . self::escapeHtml($adminNote) . '</p>' : '')
            . '<p>Jika ada pertanyaan, silakan hubungi tim Nurture For Growth.</p>'
            . '</div>';

        if (!empty($recipientUserEmail = $toPatient ? ($patientUser['email'] ?? '') : ($providerUser['email'] ?? ''))) {
            self::sendEmail($recipientUserEmail, $recipientName, $subject, $html, $message);
        }

        $number = self::normalizeWhatsappNumber($toPatient
            ? ($patientUser['phone'] ?? $patientUser['whatsapp_number'] ?? null)
            : ($providerUser['phone'] ?? $providerUser['whatsapp_number'] ?? null));
        if ($number) {
            if (self::isWhatsAppApiEnabled()) {
                self::sendWhatsApp($number, $message);
            } else {
                Logger::info('WhatsApp API tidak aktif. Keputusan refund untuk ' . $recipientName . ' dikirim via email saja.');
            }
        }

        if (($toPatient ? $patientUserId : $providerUserId) > 0) {
            Notification::create([
                'user_id' => $toPatient ? $patientUserId : $providerUserId,
                'title' => $decision === 'approved' ? 'Refund Disetujui' : 'Pengajuan Pembatalan Ditolak',
                'message' => $decision === 'approved'
                    ? 'Pengajuan pembatalan sesi konseling Anda disetujui. Kami akan memproses refund Anda.'
                    : 'Pengajuan pembatalan sesi konseling Anda ditolak.' . ($adminNote !== '' ? ' Catatan: ' . $adminNote : ''),
                'type' => 'cancellation',
                'url' => base_url('/consultation/' . $consultation['id']),
            ]);
        }
    }

    private static function buildCancellationHtml(
        string $recipientName,
        string $summary,
        string $refundLine,
        bool $toPatient,
        string $otherName,
        string $schedule
    ): string
    {
        $intro = $toPatient
            ? "Sesi konseling Anda bersama <strong>" . self::escapeHtml($otherName) . "</strong> pada <strong>" . self::escapeHtml($schedule) . "</strong> telah dibatalkan."
            : "Sesi konseling bersama client <strong>" . self::escapeHtml($otherName) . "</strong> pada <strong>" . self::escapeHtml($schedule) . "</strong> telah dibatalkan.";

        return '<div style="font-family:Arial, sans-serif; color:#333;">'
            . '<h2 style="color:#2f855a;">Sesi Konseling Dibatalkan</h2>'
            . '<p>Halo ' . self::escapeHtml($recipientName) . ',</p>'
            . '<p>' . $intro . '</p>'
            . '<p>Berikut detail sesi:</p>'
            . '<pre style="font-family:Consolas,monospace;font-size:13px;background:#f6f9f7;padding:12px;border-radius:8px;">' . self::escapeHtml($summary) . '</pre>'
            . ($refundLine !== '' ? '<p>' . nl2br(self::escapeHtml(trim($refundLine))) . '</p>' : '')
            . '<p>Jika ada pertanyaan, silakan hubungi tim Nurture For Growth.</p>'
            . '</div>';
    }

    public static function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody, string $plainBody = '', array $attachments = []): bool
    {
        $smtpConfig = self::smtpConfig();
        $fromEmail = $smtpConfig['from'] ?? 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $fromName = Setting::get('app_name', 'NFG');

        if ($smtpConfig && !empty($smtpConfig['host'])) {
            return self::sendEmailViaSmtp($smtpConfig, $fromEmail, $fromName, $toEmail, $subject, $htmlBody, $plainBody, $attachments);
        }

        $boundary = '==NFG-' . md5(uniqid((string) time(), true));
        $headers = [];
        $headers[] = 'From: ' . self::encodeHeader($fromName) . ' <' . $fromEmail . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: ' . (!empty($attachments) ? 'multipart/mixed' : 'multipart/alternative') . '; boundary="' . $boundary . '"';
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        if (empty($attachments)) {
            $body = '--' . $boundary . "\r\n";
            $body .= 'Content-Type: text/plain; charset=UTF-8\r\n';
            $body .= 'Content-Transfer-Encoding: 7bit\r\n\r\n';
            $body .= $plainBody . "\r\n\r\n";
            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Type: text/html; charset=UTF-8\r\n';
            $body .= 'Content-Transfer-Encoding: 7bit\r\n\r\n';
            $body .= $htmlBody . "\r\n\r\n";
            $body .= '--' . $boundary . '--';
        } else {
            $body = '--' . $boundary . "\r\n";
            $body .= 'Content-Type: multipart/alternative; boundary="ALT-' . $boundary . '"\r\n\r\n';
            $body .= '--ALT-' . $boundary . "\r\n";
            $body .= 'Content-Type: text/plain; charset=UTF-8\r\n';
            $body .= 'Content-Transfer-Encoding: 7bit\r\n\r\n';
            $body .= $plainBody . "\r\n\r\n";
            $body .= '--ALT-' . $boundary . "\r\n";
            $body .= 'Content-Type: text/html; charset=UTF-8\r\n';
            $body .= 'Content-Transfer-Encoding: 7bit\r\n\r\n';
            $body .= $htmlBody . "\r\n\r\n";
            $body .= '--ALT-' . $boundary . '--\r\n\r\n';

            foreach ($attachments as $attachment) {
                if (empty($attachment['path']) || !file_exists($attachment['path'])) {
                    continue;
                }
                $content = chunk_split(base64_encode(file_get_contents($attachment['path'])));
                $filename = $attachment['name'] ?? basename($attachment['path']);
                $mime = $attachment['mime'] ?? 'application/octet-stream';

                $body .= '--' . $boundary . "\r\n";
                $body .= 'Content-Type: ' . $mime . '; name="' . $filename . '"\r\n';
                $body .= 'Content-Transfer-Encoding: base64\r\n';
                $body .= 'Content-Disposition: attachment; filename="' . $filename . '"\r\n\r\n';
                $body .= $content . "\r\n\r\n";
            }

            $body .= '--' . $boundary . '--';
        }

        $success = mail($toEmail, $subject, $body, implode("\r\n", $headers));
        if (!$success) {
            Logger::warning("Email fallback failed: {$toEmail}");
        }
        return $success;
    }

    public static function sendWhatsApp(string $recipientNumber, string $message): bool
    {
        if (!self::isWhatsAppApiEnabled()) {
            Logger::warning('WhatsApp API is not active. Skipping WhatsApp message to ' . $recipientNumber);
            return false;
        }

        $cleanNumber = self::normalizeWhatsappNumber($recipientNumber);
        if (!$cleanNumber) {
            Logger::warning('Invalid WhatsApp recipient number: ' . $recipientNumber);
            return false;
        }

        $integration = IntegrationConfig::byProvider('whatsapp');
        $config = json_decode($integration['config_json'] ?? '{}', true) ?: [];
        $apiUrl = trim($config['api_url'] ?? '');
        $token = trim($config['token'] ?? '');
        $sessionId = trim($config['session_id'] ?? '');
        if (!$apiUrl) {
            Logger::warning('WhatsApp API URL is not configured. Skipping WhatsApp message to ' . $recipientNumber);
            return false;
        }

        $chatId = $cleanNumber . '@c.us';
        $payload = json_encode(['chatId' => $chatId, 'text' => $message]);

        if ($sessionId) {
            if (!str_contains($apiUrl, '/sessions/') && !str_contains($apiUrl, '/messages/send-text')) {
                $apiUrl = rtrim($apiUrl, '/') . '/sessions/' . rawurlencode($sessionId) . '/messages/send-text';
            }
        } elseif (!str_contains($apiUrl, '/messages/send-text')) {
            Logger::warning('WhatsApp session id is not configured for OpenWA. Skipping WhatsApp message to ' . $recipientNumber);
            return false;
        }

        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'X-API-Key: ' . $token;
        }

        $response = self::httpPost($apiUrl, $payload, $headers);
        if ($response === false) {
            Logger::warning('WhatsApp request failed for ' . $recipientNumber);
            return false;
        }

        Logger::info('WhatsApp message sent to ' . $recipientNumber);
        return true;
    }

    public static function sendWhatsAppMedia(string $recipientNumber, string $caption, string $fileUrl, string $filename, string $mimeType): bool
    {
        if (!self::isWhatsAppApiEnabled()) {
            Logger::warning('WhatsApp API is not active. Skipping WhatsApp media message to ' . $recipientNumber);
            return false;
        }

        $cleanNumber = self::normalizeWhatsappNumber($recipientNumber);
        if (!$cleanNumber) {
            Logger::warning('Invalid WhatsApp recipient number: ' . $recipientNumber);
            return false;
        }

        $integration = IntegrationConfig::byProvider('whatsapp');
        $config = json_decode($integration['config_json'] ?? '{}', true) ?: [];
        $apiUrl = trim($config['api_url'] ?? '');
        $token = trim($config['token'] ?? '');
        $sessionId = trim($config['session_id'] ?? '');
        if (!$apiUrl) {
            Logger::warning('WhatsApp API URL is not configured. Skipping WhatsApp media message to ' . $recipientNumber);
            return false;
        }

        $chatId = $cleanNumber . '@c.us';
        $payload = json_encode([
            'chatId' => $chatId,
            'file' => $fileUrl,
            'filename' => $filename,
            'caption' => $caption,
            'mimetype' => $mimeType,
        ]);

        if ($sessionId) {
            if (!str_contains($apiUrl, '/sessions/') && !str_contains($apiUrl, '/messages/send-file')) {
                $apiUrl = rtrim($apiUrl, '/') . '/sessions/' . rawurlencode($sessionId) . '/messages/send-file';
            }
        } elseif (!str_contains($apiUrl, '/messages/send-file')) {
            $apiUrl = rtrim($apiUrl, '/') . '/messages/send-file';
        }

        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'X-API-Key: ' . $token;
        }

        $response = self::httpPost($apiUrl, $payload, $headers);
        if ($response === false) {
            Logger::warning('WhatsApp media request failed for ' . $recipientNumber);
            return false;
        }

        Logger::info('WhatsApp media message sent to ' . $recipientNumber);
        return true;
    }

    public static function isWhatsAppApiEnabled(): bool
    {
        $integration = IntegrationConfig::byProvider('whatsapp');
        if (!$integration || !$integration['is_active']) {
            return false;
        }

        $config = json_decode($integration['config_json'] ?? '{}', true) ?: [];
        return !empty(trim($config['api_url'] ?? ''));
    }

    public static function createWhatsAppChatUrl(string $recipientNumber, string $message): string
    {
        $cleanNumber = self::normalizeWhatsappNumber($recipientNumber);
        if (!$cleanNumber) {
            return '';
        }
        $encodedMessage = rawurlencode($message);
        return 'https://wa.me/' . $cleanNumber . '?text=' . $encodedMessage;
    }

    public static function resolvePublicUrl(string $path): string
    {
        $path = ltrim($path, '/');
        $appUrl = rtrim(config_get('app_url') ?? '', '/');
        if ($appUrl !== '') {
            return $appUrl . '/' . $path;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(base_path(), '/');
        return $scheme . '://' . $host . $basePath . '/' . $path;
    }

    public static function resolveStoragePath(string $path): ?string
    {
        $relativePath = ltrim($path, '/');
        $publicPath = realpath(__DIR__ . '/../../public/' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath));
        if ($publicPath && file_exists($publicPath)) {
            return $publicPath;
        }

        $rootPath = realpath(__DIR__ . '/../../' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath));
        if ($rootPath && file_exists($rootPath)) {
            return $rootPath;
        }

        // Fallback for any absolute path provided directly.
        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    private static function mimeTypeFromPath(string $path): string
    {
        if (!file_exists($path)) {
            return 'application/octet-stream';
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $path);
                finfo_close($finfo);
                if ($mime) {
                    return $mime;
                }
            }
        }

        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($path);
            if ($mime) {
                return $mime;
            }
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }

    private static function smtpConfig(): ?array
    {
        $integration = IntegrationConfig::byProvider('smtp');
        if ($integration && $integration['is_active']) {
            return json_decode($integration['config_json'] ?? '{}', true) ?: [];
        }

        $appConfig = require __DIR__ . '/../../config/config.php';
        $mail = $appConfig['mail'] ?? [];
        return !empty($mail['host']) ? $mail : null;
    }

    public static function sendVerificationOtpEmail(string $email, string $name, string $code): bool
    {
        $safeName = self::escapeHtml($name);
        $html = '<div style="font-family:Arial,sans-serif;color:#25313c;line-height:1.55">'
            . '<h2 style="color:#2f855a">Verifikasi email Anda</h2>'
            . '<p>Halo ' . $safeName . ',</p>'
            . '<p>Gunakan kode berikut untuk mengaktifkan akun Nurture For Growth Anda:</p>'
            . '<p style="font-size:30px;font-weight:700;letter-spacing:8px;color:#1f6f5b">' . $code . '</p>'
            . '<p>Kode berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.</p>'
            . '</div>';
        $plain = "Halo {$name},\n\nKode verifikasi Nurture For Growth Anda: {$code}\nKode berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.";

        return self::sendEmail($email, $name, 'Kode verifikasi akun Nurture For Growth', $html, $plain);
    }

    public static function sendPasswordResetEmail(string $email, string $name, string $resetUrl): bool
    {
        $safeName = self::escapeHtml($name);
        $safeUrl = self::escapeHtml($resetUrl);
        $html = '<div style="font-family:Arial,sans-serif;color:#25313c;line-height:1.55">'
            . '<h2 style="color:#2f855a">Reset password</h2>'
            . '<p>Halo ' . $safeName . ',</p>'
            . '<p>Klik tombol berikut untuk membuat password baru akun Nurture For Growth Anda. Tautan ini berlaku selama 30 menit.</p>'
            . '<p><a href="' . $safeUrl . '" style="display:inline-block;padding:12px 18px;background:#1f6f5b;color:#fff;text-decoration:none;border-radius:6px;font-weight:700">Reset Password</a></p>'
            . '<p>Jika Anda tidak meminta reset password, abaikan email ini.</p>'
            . '</div>';
        $plain = "Halo {$name},\n\nReset password Anda melalui tautan berikut (berlaku 30 menit):\n{$resetUrl}\n\nJika Anda tidak meminta reset password, abaikan email ini.";

        return self::sendEmail($email, $name, 'Reset password akun Nurture For Growth', $html, $plain);
    }

    private static function sendEmailViaSmtp(array $config, string $fromEmail, string $fromName, string $toEmail, string $subject, string $htmlBody, string $plainBody, array $attachments = []): bool
    {
        $host = trim($config['host'] ?? '');
        $port = (int) ($config['port'] ?? 587);
        $username = $config['user'] ?? null;
        $password = $config['pass'] ?? null;
        if ($host === '') {
            Logger::error('SMTP host belum dikonfigurasi.');
            return false;
        }

        $secure = ($port === 465) ? 'ssl' : 'tcp';
        $transport = $secure === 'ssl' ? 'ssl://' : '';
        $remoteSocket = $transport . $host . ':' . $port;

        $socket = @stream_socket_client($remoteSocket, $errno, $errstr, 30, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            Logger::error("SMTP connection failed: {$errno} {$errstr}");
            return false;
        }

        $response = self::smtpRead($socket);
        if (!self::smtpResponseIs($response, 220)) {
            Logger::error('SMTP greeting failed: ' . $response);
            fclose($socket);
            return false;
        }

        self::smtpCommand($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $ehloResponse = self::smtpRead($socket);
        if (!self::smtpResponseIs($ehloResponse, 250)) {
            Logger::error('SMTP EHLO failed: ' . $ehloResponse);
            fclose($socket);
            return false;
        }

        if ($secure !== 'ssl' && str_contains($ehloResponse, 'STARTTLS')) {
            self::smtpCommand($socket, 'STARTTLS');
            $response = self::smtpRead($socket);
            if (!self::smtpResponseIs($response, 220) || !stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                Logger::error('SMTP STARTTLS failed: ' . $response);
                fclose($socket);
                return false;
            }
            self::smtpCommand($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $response = self::smtpRead($socket);
            if (!self::smtpResponseIs($response, 250)) {
                Logger::error('SMTP EHLO after STARTTLS failed: ' . $response);
                fclose($socket);
                return false;
            }
        }

        if ($username && $password) {
            self::smtpCommand($socket, 'AUTH LOGIN');
            $response = self::smtpRead($socket);
            if (!self::smtpResponseIs($response, 334)) {
                Logger::error('SMTP AUTH LOGIN failed: ' . $response);
                fclose($socket);
                return false;
            }
            self::smtpCommand($socket, base64_encode($username));
            $response = self::smtpRead($socket);
            if (!self::smtpResponseIs($response, 334)) {
                Logger::error('SMTP username rejected: ' . $response);
                fclose($socket);
                return false;
            }
            self::smtpCommand($socket, base64_encode($password));
            $authResponse = self::smtpRead($socket);
            if (!self::smtpResponseIs($authResponse, 235)) {
                Logger::error('SMTP authentication failed: ' . $authResponse);
                fclose($socket);
                return false;
            }
        }

        self::smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>');
        $response = self::smtpRead($socket);
        if (!self::smtpResponseIs($response, 250)) {
            Logger::error('SMTP sender rejected: ' . $response);
            fclose($socket);
            return false;
        }
        self::smtpCommand($socket, 'RCPT TO:<' . $toEmail . '>');
        $response = self::smtpRead($socket);
        if (!self::smtpResponseIs($response, 250, 251)) {
            Logger::error('SMTP recipient rejected: ' . $response);
            fclose($socket);
            return false;
        }
        self::smtpCommand($socket, 'DATA');
        $response = self::smtpRead($socket);
        if (!self::smtpResponseIs($response, 354)) {
            Logger::error('SMTP DATA command failed: ' . $response);
            fclose($socket);
            return false;
        }

        $boundary = '==NFG-' . md5(uniqid((string) time(), true));
        $altBoundary = '==NFG-ALT-' . md5(uniqid((string) time(), true));
        $crlf = "\r\n";
        $headers = [];
        $headers[] = 'From: ' . self::encodeHeader($fromName) . ' <' . $fromEmail . '>';
        $headers[] = 'To: ' . $toEmail;
        $headers[] = 'Subject: ' . self::encodeHeader($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
        $headers[] = '';

        $data = implode($crlf, $headers) . $crlf;
        $data .= '--' . $boundary . $crlf;
        $data .= 'Content-Type: multipart/alternative; boundary="' . $altBoundary . '"' . $crlf . $crlf;
        $data .= '--' . $altBoundary . $crlf;
        $data .= 'Content-Type: text/plain; charset=UTF-8' . $crlf;
        $data .= 'Content-Transfer-Encoding: 7bit' . $crlf . $crlf;
        $data .= $plainBody . $crlf . $crlf;
        $data .= '--' . $altBoundary . $crlf;
        $data .= 'Content-Type: text/html; charset=UTF-8' . $crlf;
        $data .= 'Content-Transfer-Encoding: 7bit' . $crlf . $crlf;
        $data .= $htmlBody . $crlf . $crlf;
        $data .= '--' . $altBoundary . '--' . $crlf;

        foreach ($attachments as $attachment) {
            if (empty($attachment['path']) || !file_exists($attachment['path'])) {
                continue;
            }
            $content = chunk_split(base64_encode(file_get_contents($attachment['path'])));
            $filename = $attachment['name'] ?? basename($attachment['path']);
            $mime = $attachment['mime'] ?? 'application/octet-stream';

            $data .= '--' . $boundary . $crlf;
            $data .= 'Content-Type: ' . $mime . '; name="' . $filename . '"' . $crlf;
            $data .= 'Content-Transfer-Encoding: base64' . $crlf;
            $data .= 'Content-Disposition: attachment; filename="' . $filename . '"' . $crlf . $crlf;
            $data .= $content . $crlf . $crlf;
        }

        $data .= '--' . $boundary . '--' . $crlf . '.';

        fwrite($socket, $data . "\r\n");
        $response = self::smtpRead($socket);
        self::smtpCommand($socket, 'QUIT');
        fclose($socket);

        if (!self::smtpResponseIs($response, 250)) {
            Logger::error('SMTP DATA send failed: ' . $response);
            return false;
        }

        Logger::info('Email sent to ' . $toEmail);
        return true;
    }

    private static function smtpRead($socket): string
    {
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }

    private static function smtpCommand($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    private static function smtpResponseIs(string $response, int ...$expectedCodes): bool
    {
        $code = (int) substr(trim($response), 0, 3);
        return in_array($code, $expectedCodes, true);
    }

    private static function httpPost(string $url, string $payload, array $headers = []): bool|string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($response === false) {
                Logger::error('WhatsApp curl failed: ' . $err);
                return false;
            }
            return $response;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $payload,
                'timeout' => 30,
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            Logger::error('WhatsApp HTTP POST failed with file_get_contents');
        }
        return $response;
    }

    private static function buildBookingSummary(array $consultation, array $patientUser, array $providerUser): string
    {
        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $lines = [
            'Psikolog: ' . ($providerUser['name'] ?? '-'),
            'Pasien: ' . ($patientUser['name'] ?? '-'),
            'Tanggal & Jam: ' . $schedule,
            'Metode: ' . self::mapMethod($consultation['method'] ?? 'video'),
            'Jenis Sesi: ' . self::mapSessionType($consultation['session_type'] ?? 'individual'),
            'Peran Pasien: ' . self::mapPatientRole($consultation['patient_role'] ?? 'self'),
            'Biaya: Rp' . number_format($consultation['price_amount'] ?? 0, 0, ',', '.'),
            'Alasan: ' . trim($consultation['reason'] ?? '-') ,
            'Metode Pembayaran: ' . self::mapPaymentMethod($consultation['payment_method'] ?? 'transfer'),
            'Status: ' . ucfirst(str_replace('_', ' ', $consultation['status'] ?? 'pending')),
        ];

        if (!empty($consultation['payment_proof_path'])) {
            $lines[] = 'Bukti Transfer: ' . self::resolvePublicUrl($consultation['payment_proof_path']);
        }
        if (!empty($consultation['meeting_link'])) {
            $lines[] = 'Link Google Meet: ' . $consultation['meeting_link'];
        }

        return implode("\n", $lines);
    }

    private static function buildBookingHtml(array $consultation, array $patientUser, array $providerUser, bool $forPatient, ?string $whatsappUrl = null, ?string $targetName = null, ?string $proofUrl = null): string
    {
        $heading = $forPatient ? 'Booking Konseling Anda' : 'Permintaan Booking Konseling Baru';
        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $html = '<div style="font-family:Arial, sans-serif; color:#333;">'
            . '<h2 style="color:#2f855a;">' . self::escapeHtml($heading) . '</h2>'
            . '<p>Berikut detail booking konseling:</p>'
            . '<ul style="padding-left:18px;">'
            . '<li><strong>Psikolog:</strong> ' . self::escapeHtml($providerUser['name'] ?? '-') . '</li>'
            . '<li><strong>Pasien:</strong> ' . self::escapeHtml($patientUser['name'] ?? '-') . '</li>'
            . '<li><strong>Tanggal & Jam:</strong> ' . self::escapeHtml($schedule) . '</li>'
            . '<li><strong>Metode:</strong> ' . self::escapeHtml(self::mapMethod($consultation['method'] ?? 'video')) . '</li>'
            . '<li><strong>Jenis Sesi:</strong> ' . self::escapeHtml(self::mapSessionType($consultation['session_type'] ?? 'individual')) . '</li>';
        if (!empty($consultation['patient_role']) && $consultation['patient_role'] !== 'self') {
            $html .= '<li><strong>Peran Pasien:</strong> ' . self::escapeHtml(self::mapPatientRole($consultation['patient_role'] ?? 'self')) . '</li>';
        }
        $html .= '<li><strong>Alasan:</strong> ' . self::escapeHtml(trim($consultation['reason'] ?? '-')) . '</li>'
            . '<li><strong>Metode Pembayaran:</strong> ' . self::escapeHtml(self::mapPaymentMethod($consultation['payment_method'] ?? 'transfer')) . '</li>'
            . '<li><strong>Status:</strong> ' . self::escapeHtml(ucfirst(str_replace('_', ' ', $consultation['status'] ?? 'pending'))) . '</li>';

        if ($proofUrl) {
            $html .= '<li><strong>Bukti Transfer:</strong> <a href="' . self::escapeHtml($proofUrl) . '" target="_blank">Lihat Bukti Transfer</a></li>';
        }
        if (!empty($consultation['meeting_link'])) {
            $url = self::escapeHtml($consultation['meeting_link']);
            $html .= '<li><strong>Link Google Meet:</strong> <a href="' . $url . '" target="_blank">Gabung Google Meet</a></li>';
        }

        $html .= '</ul>';

        if ($whatsappUrl && $targetName) {
            $html .= '<p>Jika Anda ingin menghubungi melalui WhatsApp, klik <a href="' . self::escapeHtml($whatsappUrl) . '" target="_blank">chat dengan ' . self::escapeHtml($targetName) . '</a>.</p>';
        }

        $html .= '<p>Silakan masuk ke dashboard untuk melihat detail lebih lanjut.</p>';
        $html .= '</div>';
        return $html;
    }

    private static function buildApprovalHtml(array $consultation, array $patientUser, array $providerUser): string
    {
        $providerName = self::escapeHtml($providerUser['name'] ?? 'Psikolog');
        $patientName = self::escapeHtml($patientUser['name'] ?? 'Pasien');
        $schedule = $consultation['schedule_at'] ? date('d M Y H:i', strtotime($consultation['schedule_at'])) : '-';
        $method = self::escapeHtml(self::mapMethod($consultation['method'] ?? 'video'));
        $duration = (int) ($consultation['duration_minutes'] ?? 0);
        $meetingLink = $consultation['meeting_link'] ?? '';
        $meetingHtml = '';
        if ($meetingLink) {
            $url = self::escapeHtml($meetingLink);
            $meetingHtml = '<li><strong>Link Meeting:</strong> <a href="' . $url . '" target="_blank" style="color:#2f855a;">Gabung Meeting</a></li>';
        }
        $paymentBadge = ($consultation['payment_status'] ?? '') === 'paid'
            ? '<span style="display:inline-block;background:#10b981;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;">LUNAS</span>'
            : '<span style="display:inline-block;background:#f59e0b;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;">' . self::escapeHtml(ucfirst(str_replace('_', ' ', $consultation['payment_status'] ?? 'pending'))) . '</span>';

        $html = '<div style="font-family:Arial,sans-serif;color:#333;">'
            . '<h2 style="color:#2f855a;">Booking Konseling Disetujui &#10003;</h2>'
            . '<p>Halo ' . $patientName . ', booking konseling Anda dengan ' . $providerName . ' telah disetujui. Berikut detailnya:</p>'
            . '<ul style="padding-left:18px;">'
            . '<li><strong>Psikolog:</strong> ' . $providerName . '</li>'
            . '<li><strong>Jadwal:</strong> ' . self::escapeHtml($schedule) . '</li>'
            . '<li><strong>Metode:</strong> ' . $method . '</li>'
            . '<li><strong>Durasi:</strong> ' . $duration . ' menit</li>'
            . $meetingHtml
            . '<li><strong>Status Pembayaran:</strong> ' . $paymentBadge . '</li>'
            . '</ul>'
            . '<p style="margin-top:18px;">Silakan cek detail booking di aplikasi untuk informasi lebih lanjut.</p>'
            . '<p style="color:#6b7280;font-size:13px;margin-top:24px;">Jika ada pertanyaan, hubungi klinik melalui WhatsApp atau telepon.</p>'
            . '</div>';
        return $html;
    }

    private static function buildFollowUpHtml(string $patientName, string $providerName, string $schedule, string $planLabel, string $targetLabel): string
    {
        $html = '<div style="font-family:Arial,sans-serif;color:#333;">'
            . '<h2 style="color:#2f855a;">Rencana Tindak Lanjut</h2>'
            . '<p>Halo ' . self::escapeHtml($patientName) . ', psikolog Anda ' . self::escapeHtml($providerName) . ' telah menyimpan rencana tindak lanjut untuk sesi konseling Anda.</p>'
            . '<table style="border-collapse:collapse;width:100%;max-width:520px;margin-top:14px;">'
            . '<tr><td style="padding:10px 12px;background:#f3f6f4;border:1px solid #e2e8f0;font-weight:700;width:150px;">Sesi</td><td style="padding:10px 12px;border:1px solid #e2e8f0;">' . self::escapeHtml($schedule) . '</td></tr>'
            . '<tr><td style="padding:10px 12px;background:#f3f6f4;border:1px solid #e2e8f0;font-weight:700;">Rencana</td><td style="padding:10px 12px;border:1px solid #e2e8f0;white-space:pre-line;">' . self::escapeHtml($planLabel) . '</td></tr>'
            . '<tr><td style="padding:10px 12px;background:#f3f6f4;border:1px solid #e2e8f0;font-weight:700;">Target</td><td style="padding:10px 12px;border:1px solid #e2e8f0;">' . self::escapeHtml($targetLabel) . '</td></tr>'
            . '</table>'
            . '<p style="margin-top:18px;">Silakan cek detail lengkapnya di aplikasi. Terima kasih.</p>'
            . '</div>';
        return $html;
    }

    private static function buildExtensionRequestHtml(
        string $patientName,
        string $providerName,
        string $schedule,
        int $extensionMinutes,
        int $extensionPrice,
        string $payUrl,
        string $reasonLine
    ): string {
        $reasonRows = $reasonLine !== ''
            ? '<tr><td style="padding:10px 12px;background:#f3f6f4;border:1px solid #e2e8f0;font-weight:700;">Alasan</td>'
                . '<td style="padding:10px 12px;border:1px solid #e2e8f0;white-space:pre-line;">' . self::escapeHtml(trim(str_replace("\nAlasan: ", '', $reasonLine))) . '</td></tr>'
            : '';

        $html = '<div style="font-family:Arial,sans-serif;color:#333;">'
            . '<h2 style="color:#2f855a;">Permintaan Perpanjangan Durasi</h2>'
            . '<p>Halo ' . self::escapeHtml($patientName) . ', psikolog Anda ' . self::escapeHtml($providerName) . ' mengajukan perpanjangan durasi untuk sesi konseling Anda pada ' . self::escapeHtml($schedule) . '.</p>'
            . '<table style="border-collapse:collapse;width:100%;max-width:520px;margin-top:14px;">'
            . '<tr><td style="padding:10px 12px;background:#f3f6f4;border:1px solid #e2e8f0;font-weight:700;width:150px;">Durasi Tambahan</td><td style="padding:10px 12px;border:1px solid #e2e8f0;">' . (int) $extensionMinutes . ' menit</td></tr>'
            . '<tr><td style="padding:10px 12px;background:#f3f6f4;border:1px solid #e2e8f0;font-weight:700;">Biaya Tambahan</td><td style="padding:10px 12px;border:1px solid #e2e8f0;">Rp' . self::escapeHtml(number_format($extensionPrice, 0, ',', '.')) . '</td></tr>'
            . $reasonRows
            . '</table>'
            . '<p style="margin-top:16px;">Perpanjangan hanya berlaku setelah Anda menyelesaikan pembayaran tambahan.</p>'
            . '<p style="margin:18px 0;"><a href="' . self::escapeHtml($payUrl) . '" style="background:#0a7d5a;color:#fff;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:700;">Selesaikan Pembayaran</a></p>'
            . '<p>Jika tombol tidak berfungsi, salin tautan berikut: ' . self::escapeHtml($payUrl) . '</p>'
            . '<p style="margin-top:18px;">Terima kasih.</p>'
            . '</div>';
        return $html;
    }

    private static function buildExtensionPaymentVerifiedHtml(
        string $patientName,
        string $providerName,
        string $schedule
    ): string {
        $html = '<div style="font-family:Arial,sans-serif;color:#333;">'
            . '<h2 style="color:#2f855a;">Pembayaran Perpanjangan Diterima</h2>'
            . '<p>Halo ' . self::escapeHtml($patientName) . ', pembayaran perpanjangan durasi untuk sesi konseling Anda bersama ' . self::escapeHtml($providerName) . ' pada ' . self::escapeHtml($schedule) . ' telah kami terima.</p>'
            . '<p>Psikolog Anda akan segera menyetujui perpanjangan dan durasi sesi akan diperbarui.</p>'
            . '<p style="margin-top:18px;">Terima kasih.</p>'
            . '</div>';
        return $html;
    }

    private static function buildExtensionApprovedHtml(
        string $patientName,
        string $providerName,
        string $schedule,
        int $extensionMinutes,
        int $extensionPrice
    ): string {
        $priceRow = $extensionPrice > 0
            ? '<tr><td style="padding:10px 12px;background:#f3f6f4;border:1px solid #e2e8f0;font-weight:700;">Biaya Tambahan</td><td style="padding:10px 12px;border:1px solid #e2e8f0;">Rp' . self::escapeHtml(number_format($extensionPrice, 0, ',', '.')) . '</td></tr>'
            : '';

        $html = '<div style="font-family:Arial,sans-serif;color:#333;">'
            . '<h2 style="color:#2f855a;">Perpanjangan Disetujui</h2>'
            . '<p>Permintaan perpanjangan sesi konseling Anda bersama ' . self::escapeHtml($providerName) . ' pada ' . self::escapeHtml($schedule) . ' telah disetujui.</p>'
            . '<table style="border-collapse:collapse;width:100%;max-width:520px;margin-top:14px;">'
            . '<tr><td style="padding:10px 12px;background:#f3f6f4;border:1px solid #e2e8f0;font-weight:700;width:150px;">Durasi Bertambah</td><td style="padding:10px 12px;border:1px solid #e2e8f0;">' . (int) $extensionMinutes . ' menit</td></tr>'
            . $priceRow
            . '</table>'
            . '<p style="margin-top:18px;">Terima kasih.</p>'
            . '</div>';
        return $html;
    }

    private static function buildExtensionRejectedHtml(
        string $patientName,
        string $providerName,
        string $schedule
    ): string {
        $html = '<div style="font-family:Arial,sans-serif;color:#333;">'
            . '<h2 style="color:#2f855a;">Perpanjangan Ditolak</h2>'
            . '<p>Halo ' . self::escapeHtml($patientName) . ', permintaan perpanjangan sesi konseling Anda bersama ' . self::escapeHtml($providerName) . ' pada ' . self::escapeHtml($schedule) . ' telah ditolak.</p>'
            . '<p>Sesi tetap berjalan sesuai durasi semula.</p>'
            . '<p style="margin-top:18px;">Terima kasih.</p>'
            . '</div>';
        return $html;
    }

    private static function mapMethod(string $method): string
    {
        return match ($method) {
            'voice' => 'Voice Call',
            'chat' => 'Chat',
            'tatap_muka' => 'Tatap Muka',
            default => 'Video Call',
        };
    }

    private static function mapSessionType(string $sessionType): string
    {
        return match ($sessionType) {
            'couple' => 'Suami & Istri',
            'family' => 'Ayah & Ibu',
            default => 'Pasien Anak / Individu',
        };
    }

    private static function mapPatientRole(string $patientRole): string
    {
        return match ($patientRole) {
            'husband' => 'Suami',
            'wife' => 'Istri',
            'father' => 'Ayah',
            'mother' => 'Ibu',
            'child' => 'Anak',
            default => 'Individu',
        };
    }

    private static function mapPaymentMethod(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'card' => 'Kartu Kredit/Debit',
            'e_wallet' => 'E-Wallet',
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            default => 'Transfer Bank',
        };
    }

    private static function normalizeWhatsappNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }
        $normalized = preg_replace('/[^0-9]/', '', $number);
        if ($normalized === '') {
            return null;
        }
        if (str_starts_with($normalized, '0')) {
            $normalized = '62' . substr($normalized, 1);
        } elseif (str_starts_with($normalized, '8')) {
            $normalized = '62' . $normalized;
        }
        return $normalized;
    }

    private static function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
