<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Journal;
use App\Models\Mood;
use App\Services\MoodService;

class WellnessController extends Controller
{
    private MoodService $moodService;

    public function __construct()
    {
        $this->moodService = new MoodService();
    }

    /**
     * Display combined Journal & Mood Tracker Dashboard
     */
    public function index(): void
    {
        $patientId = current_patient_id();
        
        // Get journals and mood data
        $journals = Journal::forPatient($patientId);
        $history = $this->moodService->history($patientId, 30); // Last 30 mood records
        $weekly = $this->moodService->chartData($patientId, 'weekly');
        $monthly = $this->moodService->chartData($patientId, 'monthly');

        $this->view('wellness.index', compact('journals', 'history', 'weekly', 'monthly'));
    }
}
