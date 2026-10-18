<?php
namespace App\Controllers;

use Core\Controller;
use Core\Localization;
use Core\Auth;
use Core\Session;

class LocalizationController extends Controller
{
    public function setLanguage($params = []): void
    {
        $language = $this->input('language') ?: ($params['language'] ?? 'id');

        if (!in_array($language, ['id', 'en'])) {
            Session::flash('error', 'Bahasa tidak didukung.');
            $this->redirect('/');
            return;
        }

        Localization::setLocale($language);

        $referrer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        Session::flash('success', 'Bahasa berhasil diubah.');

        header('Location: ' . $referrer);
        exit;
    }

    public function getLanguage(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'current' => Localization::getCurrentLocale(),
            'supported' => Localization::getSupportedLocales(),
        ]);
    }
}
