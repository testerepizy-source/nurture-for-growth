<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\ConcernTag;
use App\Services\SmartMatchingService;

class MatchingController extends Controller
{
    private SmartMatchingService $service;
    public function __construct() { $this->service = new SmartMatchingService(); }

    public function intake(): void
    {
        $tags = ConcernTag::active();
        $this->view('matching.intake', ['tags' => $tags]);
    }

    public function suggestTags(): void
    {
        $text = $this->input('text', '');
        $suggested = $this->service->suggestTagsFromText($text);
        $this->json(['tags' => $suggested]);
    }

    public function process(): void
    {
        $this->requireCsrf();
        $tagIds = $_POST['tags'] ?? [];
        $freeText = $this->input('free_text', '');

        if (empty($tagIds) && trim($freeText) === '') {
            Session::flash('error', 'Pilih minimal satu keluhan atau ceritakan singkat kondisi Anda.');
            $this->redirect('/matching');
        }

        $outcome = $this->service->match(current_patient_id(), $tagIds, $freeText);
        audit('create', 'smart_matching', 'Client menjalankan Smart Matching Engine');

        Session::set('_sme_results', $outcome);
        $this->redirect('/matching/results');
    }

    public function results(): void
    {
        $outcome = Session::get('_sme_results');
        if (!$outcome) { $this->redirect('/matching'); }
        $this->view('matching.results', $outcome);
    }
}
