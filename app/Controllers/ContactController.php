<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\ContactMessage;
use App\Models\ConcernTag;
use App\Models\Psychologist;
use App\Models\Psychiatrist;
use App\Models\EmergencyContact;

class ContactController extends Controller
{
    public function show(): void
    {
        $tags = ConcernTag::active();
        $providers = array_merge(Psychologist::allWithUser(), Psychiatrist::allWithUser());
        $emergencyContacts = EmergencyContact::active();
        $this->view('contact.index', compact('tags', 'providers', 'emergencyContacts'), 'public');
    }

    public function submit(): void
    {
        $this->requireCsrf();
        ContactMessage::create([
            'name'                  => $this->input('name'),
            'email'                 => $this->input('email'),
            'phone'                 => $this->input('phone'),
            'subject'               => $this->input('subject'),
            'service_interest'      => $this->input('service_interest'),
            'preferred_provider_id' => $this->input('preferred_provider_id') ?: null,
            'preferred_date'        => $this->input('preferred_date') ?: null,
            'preferred_time'        => $this->input('preferred_time') ?: null,
            'message'               => $this->input('message'),
        ]);
        Session::flash('success', 'Pesan Anda berhasil dikirim. Tim kami akan segera menghubungi Anda.');
        $this->redirect('/contact');
    }
}
