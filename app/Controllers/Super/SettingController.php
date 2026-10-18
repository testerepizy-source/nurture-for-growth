<?php
namespace App\Controllers\Super;

use Core\Controller;
use Core\Session;
use App\Models\Setting;
use App\Models\IntegrationConfig;

class SettingController extends Controller
{
    public function index(): void
    {
        $settings = [
            'app_name' => Setting::get('app_name'),
            'app_timezone' => Setting::get('app_timezone'),
            'app_language' => Setting::get('app_language'),
            'maintenance_mode' => Setting::get('maintenance_mode', '0'),
        ];
        $smtp = json_decode(IntegrationConfig::byProvider('smtp')['config_json'] ?? '{}', true) ?: [];
        $whatsapp = json_decode(IntegrationConfig::byProvider('whatsapp')['config_json'] ?? '{}', true) ?: [];
        $google = json_decode(IntegrationConfig::byProvider('google')['config_json'] ?? '{}', true) ?: [];
        $ai = json_decode(IntegrationConfig::byProvider('ai')['config_json'] ?? '{}', true) ?: [];
        $aiActive = IntegrationConfig::byProvider('ai')['is_active'] ?? 0;
        $smtpActive = IntegrationConfig::byProvider('smtp')['is_active'] ?? 0;
        $waActive = IntegrationConfig::byProvider('whatsapp')['is_active'] ?? 0;
        $googleActive = IntegrationConfig::byProvider('google')['is_active'] ?? 0;

        $this->view('super.settings.index', compact('settings', 'smtp', 'whatsapp', 'google', 'ai', 'aiActive', 'smtpActive', 'waActive', 'googleActive'));
    }

    public function updateGeneral(): void
    {
        $this->requireCsrf();
        Setting::set('app_name', $this->input('app_name'));
        Setting::set('app_timezone', $this->input('app_timezone'));
        Setting::set('app_language', $this->input('app_language'));
        Setting::set('maintenance_mode', $this->input('maintenance_mode', '0'));
        audit('update', 'setting', 'Super Admin memperbarui pengaturan umum');
        Session::flash('success', 'Pengaturan umum berhasil disimpan.');
        $this->redirect('/super/settings');
    }

    public function updateIntegration(array $params): void
    {
        $this->requireCsrf();
        $provider = $params['provider'];
        $allowed = ['smtp', 'whatsapp', 'google', 'ai'];
        if (!in_array($provider, $allowed, true)) { $this->redirect('/super/settings'); }

        $config = [];
        foreach ($_POST as $key => $val) {
            if ($key === '_csrf') continue;
            $config[$key] = $val;
        }

        $existing = IntegrationConfig::byProvider($provider);
        $isActive = (int) $this->input('is_active', 0);
        unset($config['is_active']);

        // Password fields are intentionally not pre-filled in the settings form.
        // Keep the existing SMTP password when the administrator leaves it blank.
        if ($provider === 'smtp' && $existing && empty($config['pass'])) {
            $existingConfig = json_decode($existing['config_json'] ?? '{}', true) ?: [];
            if (!empty($existingConfig['pass'])) {
                $config['pass'] = $existingConfig['pass'];
            }
        }
        if ($provider === 'google' && $existing) {
            $existingConfig = json_decode($existing['config_json'] ?? '{}', true) ?: [];
            foreach (['client_secret', 'refresh_token'] as $key) {
                if (empty($config[$key]) && !empty($existingConfig[$key])) {
                    $config[$key] = $existingConfig[$key];
                }
            }
        }

        if ($existing) {
            IntegrationConfig::update($existing['id'], ['config_json' => json_encode($config), 'is_active' => $isActive]);
        } else {
            IntegrationConfig::create(['provider' => $provider, 'config_json' => json_encode($config), 'is_active' => $isActive]);
        }

        audit('update', 'integration', "Super Admin memperbarui konfigurasi integrasi: {$provider}");
        Session::flash('success', 'Konfigurasi ' . strtoupper($provider) . ' berhasil disimpan. Kredensial belum benar-benar terhubung sampai Anda mengisi data valid dan aktivasi fitur pengiriman terkait diimplementasikan.');
        $this->redirect('/super/settings');
    }
}
