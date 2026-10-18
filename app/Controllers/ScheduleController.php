<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Psychologist;
use App\Models\Psychiatrist;

class ScheduleController extends Controller
{
    public function index(): void
    {
        $schedules = Schedule::forProvider(Auth::id());
        $this->view('schedule.index', ['schedules' => $schedules]);
    }

    public function apiSlots(): void
    {
        $providerId = (int) ($_GET['provider_id'] ?? 0);
        $provider = User::find($providerId);
        $psy = $provider ? Psychologist::byUserId($providerId) : null;
        $psi = $provider ? Psychiatrist::byUserId($providerId) : null;
        if (!$provider || (!$psy && !$psi) || $providerId <= 0) {
            $this->json(['ok' => false, 'error' => 'invalid_provider'], 404);
            return;
        }
        $this->json([
            'ok' => true,
            'provider_id' => $providerId,
            'slots' => Schedule::getAvailableSlots($providerId, 30),
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $mode = $this->input('mode', 'both');
        if (!in_array($mode, ['online', 'offline', 'both'], true)) {
            $mode = 'both';
        }
        Schedule::create([
            'user_id' => Auth::id(),
            'day_of_week' => (int) $this->input('day_of_week'),
            'start_time' => $this->input('start_time'),
            'end_time' => $this->input('end_time'),
            'slot_duration_minutes' => (int) $this->input('slot_duration_minutes', 30),
            'mode' => $mode,
            'is_active' => 1,
        ]);
        audit('create', 'schedule', 'Menambahkan jadwal praktik');
        Session::flash('success', 'Jadwal berhasil ditambahkan.');
        $this->redirect('/schedule');
    }

    public function delete(array $params): void
    {
        $schedule = Schedule::find((int) $params['id']);
        if ($schedule && (int) $schedule['user_id'] === Auth::id()) {
            Schedule::delete($schedule['id']);
            audit('delete', 'schedule', 'Menghapus jadwal ID ' . $schedule['id']);
        }
        $this->redirect('/schedule');
    }

    public function toggle(array $params): void
    {
        $schedule = Schedule::find((int) $params['id']);
        if ($schedule && (int) $schedule['user_id'] === Auth::id()) {
            Schedule::update($schedule['id'], ['is_active' => $schedule['is_active'] ? 0 : 1]);
        }
        $this->redirect('/schedule');
    }
}
