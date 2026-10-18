<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Auth;
use App\Models\PasswordReset;
use App\Services\AuthService;
use App\Services\CommunicationService;

class AuthController extends Controller
{
    private AuthService $service;
    public function __construct() { $this->service = new AuthService(); }

    public function showLogin(): void
    {
        $this->view('auth.login', [], 'auth');
    }

    public function login(): void
    {
        $this->requireCsrf();
        $email = $this->input('email');
        $password = $this->input('password');
        $remember = (bool) $this->input('remember');

        // simple math-captcha check
        if ((int) $this->input('captcha_answer') !== (int) Session::get('captcha_result')) {
            Session::flash('error', 'Jawaban captcha salah.');
            $this->redirect('/login');
        }

        $result = $this->service->login($email, $password, $remember);
        if (!$result['success']) {
            if (!empty($result['requires_verification'])) {
                Session::set('pending_verification_email', $result['email']);
                Session::flash('error', $result['message']);
                $this->redirect('/verify-email');
            }
            Session::flash('error', $result['message']);
            $this->redirect('/login');
        }

        audit('login', 'auth', 'User login: ' . $email);
        $this->redirect('/dashboard');
    }

    public function showRegister(): void
    {
        $this->view('auth.register', [], 'auth');
    }

    public function register(): void
    {
        $this->requireCsrf();
        $result = $this->service->register($_POST);
        if (!$result['success']) {
            Session::flash('error', implode(' ', $result['errors']));
            $_SESSION['_old'] = $_POST;
            $this->redirect('/register');
        }
        Session::set('pending_verification_email', $result['email']);
        Session::flash('success', $result['email_sent']
            ? 'Registrasi berhasil. Kode OTP telah dikirim ke email Anda.'
            : 'Akun dibuat, tetapi email OTP belum terkirim. Periksa konfigurasi SMTP lalu kirim ulang kode.');
        $this->redirect('/verify-email');
    }

    public function showVerifyEmail(): void
    {
        $this->view('auth.verify_email', ['email' => Session::get('pending_verification_email', '')], 'auth');
    }

    public function verifyEmail(): void
    {
        $this->requireCsrf();
        $email = $this->input('email');
        $result = $this->service->verifyEmail($email, (string) $this->input('otp'));
        if (!$result['success']) {
            Session::set('pending_verification_email', $email);
            Session::flash('error', $result['message']);
            $this->redirect('/verify-email');
        }
        Session::remove('pending_verification_email');
        Session::flash('success', 'Email berhasil diverifikasi. Silakan masuk ke akun Anda.');
        $this->redirect('/login');
    }

    public function resendVerificationOtp(): void
    {
        $this->requireCsrf();
        $email = (string) $this->input('email', Session::get('pending_verification_email', ''));
        $result = $this->service->resendVerificationOtp($email);
        Session::set('pending_verification_email', $email);
        Session::flash($result['success'] ? 'success' : 'error', $result['success']
            ? 'Kode OTP baru telah dikirim ke email Anda.'
            : $result['message']);
        $this->redirect('/verify-email');
    }

    public function captcha(): void
    {
        $a = random_int(1, 10);
        $b = random_int(1, 10);
        Session::set('captcha_result', $a + $b);
        $this->json(['question' => "$a + $b = ?"]);
    }

    public function logout(): void
    {
        audit('logout', 'auth', 'User logout');
        Auth::logout();
        $this->redirect('/login');
    }

    public function showForgot(): void
    {
        $this->view('auth.forgot', [], 'auth');
    }

    public function forgot(): void
    {
        $this->requireCsrf();
        $email = strtolower(trim((string) $this->input('email')));
        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->service->findUserByEmail($email) : null;

        if ($user) {
            $token = bin2hex(random_bytes(32));
            PasswordReset::rawExecute('DELETE FROM password_resets WHERE email = ?', [$email]);
            PasswordReset::create([
                'email' => $email,
                'token' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 1800),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $resetUrl = $scheme . '://' . $host . base_url('/reset-password') . '?token=' . rawurlencode($token) . '&email=' . rawurlencode($email);
            CommunicationService::sendPasswordResetEmail($email, $user['name'], $resetUrl);
        }

        Session::flash('success', 'Jika email terdaftar, instruksi reset password telah dikirim. Periksa inbox dan folder spam Anda.');
        $this->redirect('/forgot-password');
    }

    public function showResetPassword(): void
    {
        $this->view('auth.reset_password', [
            'email' => (string) ($_GET['email'] ?? ''),
            'token' => (string) ($_GET['token'] ?? ''),
        ], 'auth');
    }

    public function resetPassword(): void
    {
        $this->requireCsrf();
        $email = strtolower(trim((string) $this->input('email')));
        $token = (string) $this->input('token');
        $password = (string) $this->input('password');
        $confirmation = (string) $this->input('password_confirmation');
        $reset = PasswordReset::rawOne(
            'SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at >= NOW() ORDER BY id DESC LIMIT 1',
            [$email, hash('sha256', $token)]
        );

        if (!$reset) {
            Session::flash('error', 'Tautan reset password tidak valid atau sudah kedaluwarsa.');
            $this->redirect('/forgot-password');
        }
        if (strlen($password) < 8 || $password !== $confirmation) {
            Session::flash('error', 'Password minimal 8 karakter dan konfirmasi harus sama.');
            $this->redirect('/reset-password?token=' . rawurlencode($token) . '&email=' . rawurlencode($email));
        }

        $user = $this->service->findUserByEmail($email);
        if (!$user || !$this->service->updatePassword((int) $user['id'], $password)) {
            Session::flash('error', 'Password gagal diperbarui. Silakan coba lagi.');
            $this->redirect('/forgot-password');
        }
        PasswordReset::rawExecute('DELETE FROM password_resets WHERE email = ?', [$email]);
        Session::flash('success', 'Password berhasil diperbarui. Silakan masuk menggunakan password baru.');
        $this->redirect('/login');
    }
}
