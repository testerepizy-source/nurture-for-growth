<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\JobVacancy;

/**
 * CRUD Lowongan Pekerjaan untuk Admin.
 */
class JobVacancyController extends Controller
{
    public function index(): void
    {
        $jobs = JobVacancy::raw("SELECT * FROM job_vacancies ORDER BY is_published DESC, sort_order ASC, created_at DESC");

        $editingId = (int) $this->input('edit', 0);
        $job = $editingId ? JobVacancy::find($editingId) : null;

        $this->view('admin.jobs.index', [
            'jobs'    => $jobs,
            'job'     => $job,
            'editing' => (bool) $job,
            'types'   => JobVacancy::EMPLOYMENT_TYPES,
            'modes'   => JobVacancy::WORK_MODES,
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        [$data, $error] = $this->validatedData();
        if ($error) {
            Session::flash('error', $error);
            $this->redirect('/admin/jobs');
            return;
        }
        $data['slug'] = JobVacancy::uniqueSlug($data['title']);
        $data['created_by'] = Auth::id();
        JobVacancy::create($data);
        audit('create', 'job_vacancy', 'Membuat lowongan "' . $data['title'] . '"');
        Session::flash('success', 'Lowongan berhasil dibuat.');
        $this->redirect('/admin/jobs');
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $existing = JobVacancy::find((int) ($params['id'] ?? 0));
        if (!$existing) {
            Session::flash('error', 'Lowongan tidak ditemukan.');
            $this->redirect('/admin/jobs');
            return;
        }
        [$data, $error] = $this->validatedData();
        if ($error) {
            Session::flash('error', $error);
            $this->redirect('/admin/jobs?edit=' . (int) $existing['id']);
            return;
        }
        $data['slug'] = JobVacancy::uniqueSlug($data['title'], (int) $existing['id']);
        JobVacancy::update((int) $existing['id'], $data);
        audit('update', 'job_vacancy', 'Memperbarui lowongan "' . $data['title'] . '"');
        Session::flash('success', 'Lowongan berhasil diperbarui.');
        $this->redirect('/admin/jobs');
    }

    public function toggle(array $params): void
    {
        $job = JobVacancy::find((int) ($params['id'] ?? 0));
        if (!$job) {
            Session::flash('error', 'Lowongan tidak ditemukan.');
            $this->redirect('/admin/jobs');
            return;
        }
        JobVacancy::update((int) $job['id'], ['is_published' => (int) !$job['is_published']]);
        audit('update', 'job_vacancy', (($job['is_published'] ? 'Menonaktifkan' : 'Menerbitkan')) . ' lowongan "' . $job['title'] . '"');
        Session::flash('success', $job['is_published'] ? 'Lowongan disembunyikan dari halaman karier.' : 'Lowongan diterbitkan kembali.');
        $this->redirect('/admin/jobs');
    }

    public function delete(array $params): void
    {
        $job = JobVacancy::find((int) ($params['id'] ?? 0));
        if (!$job) {
            Session::flash('error', 'Lowongan tidak ditemukan.');
            $this->redirect('/admin/jobs');
            return;
        }
        JobVacancy::delete((int) $job['id']);
        audit('delete', 'job_vacancy', 'Menghapus lowongan "' . $job['title'] . '"');
        Session::flash('success', 'Lowongan berhasil dihapus.');
        $this->redirect('/admin/jobs');
    }

    /** @return array{0: array, 1: ?string} */
    private function validatedData(): array
    {
        $title = trim((string) $this->input('title', ''));
        if ($title === '') return [[], 'Judul posisi wajib diisi.'];

        $type = $this->input('employment_type', 'full_time');
        if (!isset(JobVacancy::EMPLOYMENT_TYPES[$type])) $type = 'full_time';
        $mode = $this->input('work_mode', 'onsite');
        if (!isset(JobVacancy::WORK_MODES[$mode])) $mode = 'onsite';

        $salaryMin = $this->input('salary_min') !== null && $this->input('salary_min') !== '' ? (int) $this->input('salary_min') : null;
        $salaryMax = $this->input('salary_max') !== null && $this->input('salary_max') !== '' ? (int) $this->input('salary_max') : null;
        if ($salaryMin !== null && $salaryMax !== null && $salaryMin > $salaryMax) {
            return [[], 'Rentang gaji tidak valid: gaji minimum melebihi gaji maksimum.'];
        }
        if ($salaryMin !== null && $salaryMin <= 0) $salaryMin = null;
        if ($salaryMax !== null && $salaryMax <= 0) $salaryMax = null;

        $deadline = trim((string) $this->input('deadline_date', ''));
        if ($deadline !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) $deadline = '';

        $contactEmail = trim((string) $this->input('contact_email', ''));
        if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            return [[], 'Format email penerima lamaran tidak valid.'];
        }

        $lines = fn(string $key): ?string => self::normalizeLines((string) $this->input($key, ''));

        return [[
            'title'             => mb_substr($title, 0, 150),
            'department'        => mb_substr(trim((string) $this->input('department', '')), 0, 80) ?: null,
            'employment_type'   => $type,
            'work_mode'         => $mode,
            'location'          => mb_substr(trim((string) $this->input('location', '')), 0, 120) ?: null,
            'salary_min'        => $salaryMin,
            'salary_max'        => $salaryMax,
            'salary_visible'    => $this->input('salary_visible') === '1' ? 1 : 0,
            'short_description' => mb_substr(trim((string) $this->input('short_description', '')), 0, 255) ?: null,
            'description'       => $lines('description'),
            'requirements'      => $lines('requirements'),
            'benefits'          => $lines('benefits'),
            'contact_email'     => $contactEmail ?: null,
            'apply_note'        => mb_substr(trim((string) $this->input('apply_note', '')), 0, 255) ?: null,
            'deadline_date'     => $deadline ?: null,
            'is_published'      => $this->input('is_published') === '1' ? 1 : 0,
            'sort_order'        => max(0, (int) $this->input('sort_order', 0)),
        ], null];
    }

    private static function normalizeLines(string $text): ?string
    {
        $rows = array_values(array_filter(array_map(
            fn($line) => trim((string) preg_replace('/^[-*\x{2022}\x{00B7}]\s*/u', '', $line) ?? ''),
            explode("\n", str_replace("\r", '', $text))
        ), fn($line) => $line !== ''));
        return $rows ? implode("\n", array_slice($rows, 0, 50)) : null;
    }
}
