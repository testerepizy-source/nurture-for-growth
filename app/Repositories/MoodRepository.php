<?php
namespace App\Repositories;
use App\Models\Mood;

class MoodRepository
{
    public function forPatient(int $patientId): array { return Mood::forPatient($patientId); }

    public function upsertToday(int $patientId, array $data): void
    {
        $existing = Mood::findByPatientAndDate($patientId, $data['mood_date']);
        if ($existing) {
            Mood::update($existing['id'], $data);
        } else {
            $data['patient_id'] = $patientId;
            Mood::create($data);
        }
    }

    public function weekly(int $patientId): array { return Mood::weeklyAverage($patientId); }
    public function monthly(int $patientId): array { return Mood::monthlyAverage($patientId); }
}
