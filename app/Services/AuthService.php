<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\LoginHistory;
use App\Models\Patient;
use App\Models\EmailVerificationOtp;
use App\Services\CommunicationService;
use Core\Auth;

class AuthService
{
    private UserRepository $users;

    public function __construct() { $this->users = new UserRepository(); }

    public function findUserByEmail(string $email): ?array
    {
        return $this->users->findByEmail($email);
    }

    public function updatePassword(int $userId, string $password): bool
    {
        return $this->users->update($userId, ['password' => $password]);
    }

    public function register(array $input): array
    {
        $errors = [];
        $name = trim($input['name'] ?? '');
        $email = strtolower(trim($input['email'] ?? ''));
        if ($name === '') $errors[] = 'Nama wajib diisi.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
        if ($this->users->findByEmail($email)) $errors[] = 'Email sudah terdaftar.';
        if (empty($input['password']) || strlen($input['password']) < 8) $errors[] = 'Password minimal 8 karakter.';
        if (($input['password'] ?? '') !== ($input['password_confirmation'] ?? '')) $errors[] = 'Konfirmasi password tidak sama.';

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        // Role "pasien" (id 5 pada seeder default) untuk pendaftaran mandiri via landing page.
        $userId = $this->users->create([
            'role_id' => 5,
            'name'    => $name,
            'email'   => $email,
            'password'=> $input['password'],
            'status'  => 'active',
        ]);

        Patient::create([
            'user_id' => $userId,
            'medical_record_number' => 'MRN-' . date('Y') . '-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT),
        ]);

        $otp = $this->createVerificationOtp($userId);
        $emailSent = CommunicationService::sendVerificationOtpEmail($email, $name, $otp['code']);

        return ['success' => true, 'user_id' => $userId, 'email' => $email, 'email_sent' => $emailSent];
    }

    public function login(string $email, string $password, bool $remember = false): array
    {
        $email = strtolower(trim($email));
        $pendingUser = $this->users->findByEmail($email);
        if ($pendingUser && $pendingUser['status'] === 'active'
            && password_verify($password, $pendingUser['password'])
            && empty($pendingUser['email_verified_at'])) {
            return ['success' => false, 'requires_verification' => true, 'email' => $email,
                'message' => 'Email Anda belum diverifikasi. Masukkan kode OTP yang dikirimkan ke email.'];
        }

        $user = Auth::attempt($email, $password);

        if ($user) {
            LoginHistory::create([
                'user_id'    => $user['id'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'status'     => 'success',
            ]);
        }

        if (!$user) {
            return ['success' => false, 'message' => 'Email atau password salah, atau akun tidak aktif.'];
        }

        Auth::login($user, $remember);
        return ['success' => true, 'user' => $user];
    }

    public function verifyEmail(string $email, string $code): array
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));
        if (!$user || !empty($user['email_verified_at'])) {
            return ['success' => false, 'message' => 'Kode verifikasi tidak valid atau akun sudah terverifikasi.'];
        }

        $otp = EmailVerificationOtp::rawOne(
            'SELECT * FROM email_verification_otps WHERE user_id = ? AND verified_at IS NULL ORDER BY id DESC LIMIT 1',
            [$user['id']]
        );
        if (!$otp || strtotime($otp['expires_at']) < time()) {
            return ['success' => false, 'message' => 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang kode.'];
        }
        if ((int) $otp['attempts'] >= 5) {
            return ['success' => false, 'message' => 'Terlalu banyak percobaan. Silakan kirim ulang kode OTP.'];
        }

        EmailVerificationOtp::update($otp['id'], ['attempts' => (int) $otp['attempts'] + 1]);
        if (!preg_match('/^\d{6}$/', $code) || !password_verify($code, $otp['otp_hash'])) {
            return ['success' => false, 'message' => 'Kode OTP tidak tepat.'];
        }

        $now = date('Y-m-d H:i:s');
        $this->users->update((int) $user['id'], ['email_verified_at' => $now]);
        EmailVerificationOtp::update($otp['id'], ['verified_at' => $now]);
        return ['success' => true];
    }

    public function resendVerificationOtp(string $email): array
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));
        if (!$user || !empty($user['email_verified_at'])) {
            return ['success' => false, 'message' => 'Tidak dapat mengirim kode untuk alamat email tersebut.'];
        }

        $latest = EmailVerificationOtp::rawOne(
            'SELECT * FROM email_verification_otps WHERE user_id = ? AND verified_at IS NULL ORDER BY id DESC LIMIT 1',
            [$user['id']]
        );
        if ($latest && strtotime($latest['sent_at']) > time() - 60) {
            return ['success' => false, 'message' => 'Tunggu 60 detik sejak pengiriman terakhir sebelum meminta kode baru.'];
        }

        $otp = $this->createVerificationOtp((int) $user['id']);
        $sent = CommunicationService::sendVerificationOtpEmail($user['email'], $user['name'], $otp['code']);
        return $sent
            ? ['success' => true]
            : ['success' => false, 'message' => 'Email gagal dikirim. Periksa konfigurasi SMTP lalu coba lagi.'];
    }

    private function createVerificationOtp(int $userId): array
    {
        $code = (string) random_int(100000, 999999);
        $now = date('Y-m-d H:i:s');
        EmailVerificationOtp::create([
            'user_id' => $userId,
            'otp_hash' => password_hash($code, PASSWORD_DEFAULT),
            'expires_at' => date('Y-m-d H:i:s', time() + 600),
            'sent_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return ['code' => $code];
    }
}
