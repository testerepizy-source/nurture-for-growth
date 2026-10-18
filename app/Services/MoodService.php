<?php
namespace App\Services;

use App\Repositories\MoodRepository;

class MoodService
{
    private MoodRepository $repo;

    public function __construct() { $this->repo = new MoodRepository(); }

    public function record(int $patientId, array $input): void
    {
        $data = [
            'mood_scale' => max(1, min(10, (int) $input['mood_scale'])),
            'emoji'      => $input['emoji'] ?? null,
            'cause'      => $input['cause'] ?? null,
            'activity'   => $input['activity'] ?? null,
            'note'       => $input['note'] ?? null,
            'mood_date'  => $input['mood_date'] ?: date('Y-m-d'),
        ];
        $this->repo->upsertToday($patientId, $data);
    }

    public function history(int $patientId): array { return $this->repo->forPatient($patientId); }

    public function chartData(int $patientId, string $range = 'weekly'): array
    {
        $rows = $range === 'monthly' ? $this->repo->monthly($patientId) : $this->repo->weekly($patientId);
        return [
            'labels' => array_map(fn($r) => date('d M', strtotime($r['mood_date'])), $rows),
            'values' => array_map(fn($r) => (int) $r['mood_scale'], $rows),
        ];
    }
}
