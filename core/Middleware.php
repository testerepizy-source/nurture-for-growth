<?php

namespace Core;

class Middleware
{
    /** Require the user to be logged in, otherwise redirect to login page. */
    public static function auth(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            header('Location: ' . base_url('/login'));
            exit;
        }
    }

    /** Require the user to be a guest (not logged in). */
    public static function guest(): void
    {
        if (Auth::check()) {
            header('Location: ' . base_url('/dashboard'));
            exit;
        }
    }

    /** Require a specific permission code, otherwise show 403. */
    public static function permission(string $code): void
    {
        self::auth();
        if (!Auth::can($code)) {
            http_response_code(403);
            require __DIR__ . '/../app/Views/errors/403.php';
            exit;
        }
    }

    /** Require the user to have one of the given role names. */
    public static function role(array $roles): void
    {
        self::auth();
        if (!in_array(Auth::roleName(), $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../app/Views/errors/403.php';
            exit;
        }
    }

    /** Require medical-data access for the currently selected family profile. */
    public static function patientProfileData(): void
    {
        self::auth();
        if (Auth::roleName() !== 'pasien') return;

        $patientId = current_patient_id();
        $access = $patientId ? \App\Models\FamilyProfileAccess::accessFor(Auth::id(), $patientId) : null;
        if (!$access || !(int) $access['can_view_medical_data']) {
            Session::flash('error', 'Profil aktif tidak memiliki izin untuk mengakses data kesehatan. Pilih profil lain atau minta izin dari pengelola akun.');
            header('Location: ' . base_url('/profile'));
            exit;
        }
    }
}
