<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\Consultation;
use App\Models\ProviderReview;

class ReviewController extends Controller
{
    public function store(array $params): void
    {
        $this->requireCsrf();
        $consultationId = (int) $params['id'];
        $consultation = Consultation::find($consultationId);

        if (!$consultation || (int) $consultation['patient_id'] !== current_patient_id()) {
            http_response_code(403); require __DIR__ . '/../Views/errors/403.php'; return;
        }
        if ($consultation['status'] !== 'completed') {
            Session::flash('error', 'Ulasan hanya dapat diberikan untuk sesi yang sudah selesai.');
            $this->redirect('/consultation/' . $consultationId);
        }
        if (ProviderReview::alreadyReviewed($consultationId)) {
            Session::flash('error', 'Anda sudah memberikan ulasan untuk sesi ini.');
            $this->redirect('/consultation/' . $consultationId);
        }

        $rating = max(1, min(5, (int) $this->input('rating', 5)));

        ProviderReview::create([
            'provider_id' => $consultation['provider_id'],
            'patient_id' => current_patient_id(),
            'consultation_id' => $consultationId,
            'rating' => $rating,
            'comment' => $this->input('comment'),
            'is_published' => 1,
        ]);

        audit('create', 'provider_review', 'Client memberi ulasan untuk sesi ID ' . $consultationId);
        Session::flash('success', 'Terima kasih atas ulasan Anda!');
        $this->redirect('/consultation/' . $consultationId);
    }
}
