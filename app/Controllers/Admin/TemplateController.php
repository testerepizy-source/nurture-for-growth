<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\NotificationTemplate;
use App\Models\EmailTemplate;

class TemplateController extends Controller
{
    public function index(): void
    {
        $notificationTemplates = NotificationTemplate::all('id ASC');
        $emailTemplates = EmailTemplate::all('id ASC');
        $this->view('admin.templates.index', compact('notificationTemplates', 'emailTemplates'));
    }

    public function updateNotification(array $params): void
    {
        $this->requireCsrf();
        NotificationTemplate::update((int) $params['id'], [
            'subject' => $this->input('subject'),
            'body' => $this->input('body'),
            'is_active' => (int) $this->input('is_active', 1),
        ]);
        Session::flash('success', 'Template notifikasi berhasil diperbarui.');
        $this->redirect('/admin/templates');
    }

    public function updateEmail(array $params): void
    {
        $this->requireCsrf();
        EmailTemplate::update((int) $params['id'], [
            'subject' => $this->input('subject'),
            'body_html' => $this->input('body_html'),
            'is_active' => (int) $this->input('is_active', 1),
        ]);
        Session::flash('success', 'Template email berhasil diperbarui.');
        $this->redirect('/admin/templates');
    }
}
