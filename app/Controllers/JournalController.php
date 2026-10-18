<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\Journal;
use App\Models\Patient;
use App\Models\CrisisEvent;
use App\Models\JournalAccessGrant;

class JournalController extends Controller
{
    public function index(): void
    {
        $search = $this->input('q', '');
        $journals = Journal::forPatient(current_patient_id(), $search);
        $this->view('journal.index', ['journals' => $journals, 'search' => $search]);
    }

    public function create(): void
    {
        $this->view('journal.form', ['journal' => null]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $data = $this->collect();
        $data['patient_id'] = current_patient_id();
        $id = Journal::create($data);
        $this->syncAccess($id);
        $this->crisisCheck($data['content']);
        audit('create', 'journal', 'Menambahkan jurnal: ' . $data['title']);
        Session::flash('success', 'Jurnal berhasil disimpan.');
        $this->redirect('/journal');
    }

    public function edit(array $params): void
    {
        $journal = Journal::find((int) $params['id']);
        if (!$journal || (int) $journal['patient_id'] !== current_patient_id()) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }
        $this->view('journal.form', ['journal' => $journal]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $journal = Journal::find((int) $params['id']);
        if (!$journal || (int) $journal['patient_id'] !== current_patient_id()) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }

        $data = $this->collect();
        Journal::update($journal['id'], $data);
        $this->syncAccess($journal['id']);
        $this->crisisCheck($data['content']);
        audit('update', 'journal', 'Mengubah jurnal: ' . $data['title']);
        Session::flash('success', 'Jurnal berhasil diperbarui.');
        $this->redirect('/journal');
    }

    public function delete(array $params): void
    {
        $journal = Journal::find((int) $params['id']);
        if ($journal && (int) $journal['patient_id'] === current_patient_id()) {
            Journal::delete($journal['id']);
            audit('delete', 'journal', 'Menghapus jurnal ID ' . $journal['id']);
        }
        $this->redirect('/journal');
    }

    public function toggleFavorite(array $params): void
    {
        $journal = Journal::find((int) $params['id']);
        if ($journal && (int) $journal['patient_id'] === current_patient_id()) {
            Journal::update($journal['id'], ['is_favorite' => $journal['is_favorite'] ? 0 : 1]);
        }
        $this->redirect('/journal');
    }

    /** Provider-facing: view a patient's journal, only if access has been granted or role has journal.view_granted + explicit grant. */
    public function viewAsProvider(array $params): void
    {
        \Core\Middleware::permission('journal.view_granted');
        $journal = Journal::find((int) $params['id']);
        if (!$journal || !JournalAccessGrant::hasAccess($journal['id'], Auth::id())) {
            http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return;
        }
        $this->view('journal.provider_view', ['journal' => $journal]);
    }

    private function collect(): array
    {
        return [
            'title'        => $this->input('title'),
            'content'      => $this->input('content'),
            'tag'          => $this->input('tag'),
            'category'     => $this->input('category'),
            'is_favorite'  => (int) $this->input('is_favorite', 0),
            'is_private'   => $this->input('grant_access') ? 0 : 1,
            'journal_date' => $this->input('journal_date') ?: date('Y-m-d'),
        ];
    }

    /** If patient checks "grant access", create access grants for their assigned and/or treating psychologist/psychiatrist. */
    private function syncAccess(int $journalId): void
    {
        $patient = Patient::find(current_patient_id());

        if (!$this->input('grant_access')) {
            JournalAccessGrant::raw('DELETE FROM journal_access_grants WHERE journal_id = ?', [$journalId]);
            return;
        }

        $providerUserIds = [];
        foreach (['assigned_psychologist_id', 'assigned_psychiatrist_id'] as $col) {
            if (!empty($patient[$col])) {
                $providerUserIds[(int) $patient[$col]] = true;
            }
        }

        if (!empty($patient['id'])) {
            $rows = JournalAccessGrant::raw(
                'SELECT DISTINCT c.provider_id FROM consultations c WHERE c.patient_id = ? AND c.provider_id IS NOT NULL',
                [(int) $patient['id']]
            );
            foreach ($rows as $row) {
                if (!empty($row['provider_id'])) {
                    $providerUserIds[(int) $row['provider_id']] = true;
                }
            }
        }

        foreach (array_keys($providerUserIds) as $providerUserId) {
            if (!JournalAccessGrant::hasAccess($journalId, $providerUserId)) {
                JournalAccessGrant::create(['journal_id' => $journalId, 'granted_to_user_id' => $providerUserId]);
            }
        }
    }

    /** Basic keyword-based crisis detection; flags event for provider review, never diagnoses. */
    private function crisisCheck(string $content): void
    {
        $keyword = detect_crisis($content);
        if ($keyword) {
            CrisisEvent::create([
                'patient_id'      => current_patient_id(),
                'source'          => 'journal',
                'matched_keyword' => $keyword,
                'context_snippet' => mb_substr($content, 0, 200),
                'is_flagged'      => 1,
                'provider_notified' => 0,
            ]);
            audit('flag_high_risk', 'journal', 'Kata kunci krisis terdeteksi pada jurnal');
            Session::flash('crisis', 'Kami mendeteksi kata-kata yang mengindikasikan kondisi darurat pada tulisan Anda. Jika Anda dalam bahaya, segera hubungi layanan darurat atau lihat kontak bantuan di halaman Artikel.');
        }
    }
}
