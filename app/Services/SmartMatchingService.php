<?php
namespace App\Services;

use App\Models\ConcernTag;
use App\Models\ConcernKeyword;
use App\Models\Psychologist;
use App\Models\Psychiatrist;
use App\Models\Schedule;
use App\Models\Consultation;
use App\Models\PatientConcernSelection;
use App\Models\SmartMatchingLog;
use App\Models\CrisisEvent;

/**
 * Smart Matching Engine (SME)
 * ---------------------------------------------------------------------
 * Filosofi: pasien awam biasanya TIDAK tahu perbedaan psikolog klinis dewasa,
 * psikolog trauma, psikolog CBT, atau psikiater — mereka hanya tahu KELUHAN
 * yang dirasakan. Mengacu pada pola bicarakan.id (tag spesialisasi + pasien
 * pilih tag keluhan, bukan riset istilah klinis sendiri).
 *
 * CATATAN JUJUR: ini adalah mesin PENCOCOKAN RULE-BASED & TRANSPARAN
 * (skor dari tag overlap + urgensi + ketersediaan), BUKAN machine learning atau
 * LLM. Ini pilihan desain yang disengaja untuk konteks klinis: setiap hasil
 * rekomendasi harus bisa dijelaskan & diaudit (lihat method explainMatch()),
 * bukan kotak hitam. Skema database (tabel concern_tags/concern_keywords)
 * cukup fleksibel bila kelak ingin diganti dengan model ML/NLP yang lebih canggih.
 */
class SmartMatchingService
{
    private const WEIGHT_TAG_MATCH = 10;
    private const WEIGHT_MEDICAL_URGENCY = 15;
    private const WEIGHT_SOON_AVAILABILITY = 3;
    private const WEIGHT_LOW_CASELOAD = 2;

    /**
     * Sarankan tag dari teks bebas keluhan pasien (opsional, melengkapi pilihan tag manual).
     */
    public function suggestTagsFromText(string $freeText): array
    {
        if (trim($freeText) === '') return [];
        $tagIds = ConcernKeyword::matchTagsFromText($freeText);
        if (empty($tagIds)) return [];
        $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
        return ConcernTag::raw("SELECT * FROM concern_tags WHERE id IN ($placeholders)", $tagIds);
    }

    /**
     * Method utama: cari & ranking provider berdasarkan tag keluhan yang dipilih pasien.
     *
     * @param int   $patientId
     * @param array $tagIds     ID concern_tags yang dipilih pasien
     * @param string $freeText  Keluhan bebas (opsional, disimpan untuk histori & audit)
     * @return array{ results: array, is_urgent: bool, matched_crisis_keyword: ?string }
     */
    public function match(int $patientId, array $tagIds, string $freeText = ''): array
    {
        $tagIds = array_map('intval', $tagIds);

        // Deteksi urgensi: kata kunci krisis pada teks bebas ATAU tag "Gejala Medis Berat" dipilih.
        $crisisKeyword = $freeText !== '' ? detect_crisis($freeText) : null;
        $medicalUrgencyTag = ConcernTag::rawOne("SELECT id FROM concern_tags WHERE slug = 'gejala-medis-berat'");
        $isUrgent = $crisisKeyword !== null || ($medicalUrgencyTag && in_array((int) $medicalUrgencyTag['id'], $tagIds, true));

        if ($isUrgent) {
            CrisisEvent::create([
                'patient_id'      => $patientId,
                'source'          => 'assessment', // intake keluhan diperlakukan setara sinyal risiko
                'matched_keyword' => $crisisKeyword,
                'context_snippet' => mb_substr($freeText, 0, 200) ?: 'Tag "Gejala Medis Berat" dipilih saat intake Smart Matching',
                'is_flagged'      => 1,
                'provider_notified' => 0,
            ]);
        }

        // Simpan pilihan tag pasien untuk histori.
        foreach ($tagIds as $tagId) {
            PatientConcernSelection::create([
                'patient_id' => $patientId,
                'tag_id'     => $tagId,
                'free_text_note' => $freeText ?: null,
            ]);
        }

        $providers = array_merge(
            $this->candidateList('psikolog'),
            $this->candidateList('psikiater')
        );

        $scored = [];
        foreach ($providers as $p) {
            $providerTagIds = array_column(ConcernTag::tagsForProvider($p['user_id']), 'id');
            $overlap = count(array_intersect($tagIds, $providerTagIds));

            // Jika tidak ada tag yang cocok sama sekali, provider tetap masuk daftar
            // dengan skor dasar rendah (agar pasien selalu punya opsi), bukan disembunyikan total.
            $score = $overlap * self::WEIGHT_TAG_MATCH;

            // Urgensi medis → prioritaskan psikiater (bisa meresepkan obat).
            if ($isUrgent && $p['role'] === 'psikiater') {
                $score += self::WEIGHT_MEDICAL_URGENCY;
            }

            $nextSlot = $this->nextAvailableSlotLabel($p['user_id']);
            if ($nextSlot['days_until'] !== null) {
                $score += max(0, self::WEIGHT_SOON_AVAILABILITY - $nextSlot['days_until']);
            }

            $activeCaseload = Consultation::count(
                "provider_id = ? AND status IN ('pending','approved')", [$p['user_id']]
            );
            $score += max(0, self::WEIGHT_LOW_CASELOAD - min($activeCaseload, self::WEIGHT_LOW_CASELOAD));

            $matchedTagNames = array_column(
                array_filter(ConcernTag::tagsForProvider($p['user_id']), fn($t) => in_array((int) $t['id'], $tagIds, true)),
                'name'
            );

            $scored[] = [
                'provider' => $p,
                'score' => $score,
                'overlap' => $overlap,
                'matched_tags' => $matchedTagNames,
                'next_slot' => $nextSlot['label'],
                'active_caseload' => $activeCaseload,
                'reason' => $this->explainMatch($overlap, $matchedTagNames, $isUrgent, $p['role']),
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        $top = array_slice($scored, 0, 6);

        SmartMatchingLog::create([
            'patient_id' => $patientId,
            'input_tags_json' => json_encode($tagIds),
            'input_free_text' => $freeText ?: null,
            'suggested_provider_ids_json' => json_encode(array_column(array_column($top, 'provider'), 'user_id')),
        ]);

        return [
            'results' => $top,
            'is_urgent' => $isUrgent,
            'matched_crisis_keyword' => $crisisKeyword,
        ];
    }

    /** Catat provider mana yang akhirnya benar-benar dipilih pasien (untuk audit & evaluasi kualitas matching). */
    public function recordSelection(int $patientId, int $providerId): void
    {
        $latest = SmartMatchingLog::rawOne(
            'SELECT id FROM smart_matching_logs WHERE patient_id = ? ORDER BY created_at DESC LIMIT 1',
            [$patientId]
        );
        if ($latest) {
            SmartMatchingLog::update($latest['id'], ['selected_provider_id' => $providerId]);
        }
    }

    private function candidateList(string $role): array
    {
        $rows = $role === 'psikiater' ? Psychiatrist::allWithUser() : Psychologist::allWithUser();
        return array_map(function ($r) use ($role) {
            $r['role'] = $role;
            return $r;
        }, array_filter($rows, fn($r) => $r['status'] === 'active'));
    }

    private function explainMatch(int $overlap, array $matchedTagNames, bool $isUrgent, string $role): string
    {
        if ($overlap > 0) {
            $reason = 'Cocok untuk: ' . implode(', ', $matchedTagNames);
        } else {
            $reason = 'Tidak ada kecocokan tag spesifik — ditampilkan sebagai alternatif umum';
        }
        if ($isUrgent && $role === 'psikiater') {
            $reason .= '. Diprioritaskan karena indikasi memerlukan penanganan medis segera.';
        }
        return $reason;
    }

    /**
     * Perkiraan slot terdekat berdasarkan schedules (hari & jam praktik).
     * Estimasi sederhana berbasis hari-dalam-minggu, BUKAN pengecekan slot
     * yang sudah terisi booking lain (butuh kalkulasi kalender lebih detail
     * pada pengembangan lanjutan).
     */
    private function nextAvailableSlotLabel(int $userId): array
    {
        $schedules = Schedule::forProvider($userId);
        $activeDays = array_column(array_filter($schedules, fn($s) => $s['is_active']), 'day_of_week');
        if (empty($activeDays)) return ['label' => 'Jadwal belum tersedia', 'days_until' => null];

        $today = (int) date('w');
        $minDiff = 8;
        foreach ($activeDays as $d) {
            $diff = ((int) $d - $today + 7) % 7;
            if ($diff < $minDiff) $minDiff = $diff;
        }
        $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $label = $minDiff === 0 ? 'Hari ini' : ($minDiff === 1 ? 'Besok' : 'Sekitar ' . $minDiff . ' hari lagi');
        return ['label' => $label, 'days_until' => $minDiff];
    }
}
