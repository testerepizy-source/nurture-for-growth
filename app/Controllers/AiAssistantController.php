<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Middleware;
use App\Models\Journal;
use App\Models\AssessmentResult;
use App\Models\AiSummaryLog;
use App\Models\Article;

/**
 * AI Assistant - dibatasi secara ketat sesuai requirement:
 *   BOLEH  : merangkum journal, merangkum hasil assessment, membantu ringkasan sesi,
 *            merekomendasikan artikel edukasi.
 *   TIDAK  : memberi diagnosis, memberi resep, menggantikan psikolog/psikiater.
 *
 * CATATAN JUJUR: implementasi ini adalah rule-based summarizer sederhana yang
 * mengekstrak & merangkas kalimat kunci secara deterministik. Ini BUKAN panggilan
 * ke Large Language Model. Untuk mengaktifkan LLM sungguhan, isi kredensial pada
 * Super Admin > Integrasi API (provider "ai"), lalu implementasikan pemanggilan
 * API di method callExternalAi() di bawah ini.
 */
class AiAssistantController extends Controller
{
    public function summarizeJournal(array $params): void
    {
        Middleware::permission('ai_assistant.use');
        $journal = Journal::find((int) $params['id']);
        if (!$journal) { $this->json(['error' => 'Journal tidak ditemukan'], 404); }

        $isOwner = Auth::roleName() === 'pasien' && (int) $journal['patient_id'] === current_patient_id();
        $isGrantedProvider = \App\Models\JournalAccessGrant::hasAccess($journal['id'], Auth::id());
        if (!$isOwner && !$isGrantedProvider) {
            $this->json(['error' => 'Anda tidak memiliki akses ke jurnal ini'], 403);
        }

        $summary = $this->extractiveSummary($journal['content']);

        AiSummaryLog::create([
            'requested_by' => Auth::id(),
            'subject_type' => 'journal',
            'subject_id'   => $journal['id'],
            'prompt_used'  => 'extractive_summary_v1',
            'summary_result' => $summary,
            'disclaimer_shown' => 1,
        ]);

        $this->json([
            'summary' => $summary,
            'disclaimer' => 'Ringkasan ini dihasilkan otomatis dan hanya bersifat bantu-baca. Bukan interpretasi klinis atau diagnosis.',
        ]);
    }

    public function summarizeAssessment(array $params): void
    {
        Middleware::permission('ai_assistant.use');
        $result = AssessmentResult::detail((int) $params['id']);
        if (!$result) { $this->json(['error' => 'Hasil assessment tidak ditemukan'], 404); }

        $isOwner = Auth::roleName() === 'pasien' && (int) $result['patient_id'] === current_patient_id();
        $patient = \App\Models\Patient::find($result['patient_id']);
        $isAssignedProvider = $patient && (
            (int) ($patient['assigned_psychologist_id'] ?? 0) === Auth::id() ||
            (int) ($patient['assigned_psychiatrist_id'] ?? 0) === Auth::id()
        );
        if (!$isOwner && !$isAssignedProvider) {
            $this->json(['error' => 'Anda tidak memiliki akses ke hasil assessment ini'], 403);
        }

        $summary = sprintf(
            'Client %s menyelesaikan "%s" dengan skor total %d, berada pada kategori "%s". %s',
            $result['patient_name'],
            $result['assessment_title'],
            $result['total_score'],
            $result['category_label'] ?? '-',
            $result['is_high_risk'] ? 'Kategori ini menandakan risiko tinggi dan perlu perhatian segera dari tenaga profesional.' : 'Kategori ini berada pada tingkat risiko yang lebih rendah.'
        );

        AiSummaryLog::create([
            'requested_by' => Auth::id(),
            'subject_type' => 'assessment',
            'subject_id'   => $result['id'],
            'prompt_used'  => 'template_summary_v1',
            'summary_result' => $summary,
            'disclaimer_shown' => 1,
        ]);

        $this->json([
            'summary' => $summary,
            'disclaimer' => 'Ringkasan otomatis ini bukan diagnosis. Interpretasi klinis tetap menjadi kewenangan psikolog/psikiater.',
        ]);
    }

    public function recommendArticles(): void
    {
        Middleware::permission('ai_assistant.use');
        // Rekomendasi sederhana: artikel terbaru yang published (dapat dikembangkan
        // menjadi rekomendasi berbasis tag/kategori assessment client).
        $articles = array_slice(Article::published(), 0, 3);
        $this->json(['articles' => $articles]);
    }

    /**
     * Extractive summary sederhana: ambil 2 kalimat pertama + kalimat terpanjang
     * (heuristik "kalimat terpanjang biasanya paling informatif" untuk teks pendek).
     * Deterministik, tanpa API eksternal.
     */
    private function extractiveSummary(string $text): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (count($sentences) <= 2) return $text;

        $lead = array_slice($sentences, 0, 2);
        $longest = '';
        foreach ($sentences as $s) { if (mb_strlen($s) > mb_strlen($longest)) $longest = $s; }
        if (!in_array($longest, $lead, true)) $lead[] = $longest;

        return implode(' ', array_unique($lead));
    }

    /**
     * Titik integrasi untuk LLM API sungguhan (OpenAI/Claude/dll).
     * Sengaja dibiarkan sebagai stub agar tidak menyesatkan seolah sudah terhubung.
     */
    private function callExternalAi(string $prompt): string
    {
        // TODO: baca kredensial dari App\Models\IntegrationConfig::byProvider('ai')
        // lalu panggil API sesuai provider. Belum diimplementasikan pada starter ini.
        throw new \RuntimeException('Integrasi AI eksternal belum dikonfigurasi.');
    }
}
