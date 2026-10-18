<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index(): void
    {
        $messages = ContactMessage::recent();
        $this->view('admin.contact_messages.index', ['messages' => $messages]);
    }

    public function markRead(array $params): void
    {
        ContactMessage::update((int) $params['id'], ['is_read' => 1]);
        $this->redirect('/admin/contact-messages');
    }
}
