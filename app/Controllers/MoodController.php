<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Services\MoodService;

class MoodController extends Controller
{
    private MoodService $service;
    public function __construct() { $this->service = new MoodService(); }

    public function index(): void
    {
        $pid = current_patient_id();
        $history = $this->service->history($pid);
        $weekly = $this->service->chartData($pid, 'weekly');
        $monthly = $this->service->chartData($pid, 'monthly');
        $this->view('mood.index', ['history' => $history, 'weekly' => $weekly, 'monthly' => $monthly]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $this->service->record(current_patient_id(), $_POST);
        audit('create', 'mood', 'Client input mood harian');
        Session::flash('success', 'Mood berhasil disimpan.');
        $this->redirect(($_POST['return'] ?? '') === 'wellness' ? '/wellness' : '/mood');
    }
}
