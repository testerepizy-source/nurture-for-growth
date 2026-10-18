<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\JobVacancy;

/**
 * Halaman publik Karier / Lowongan Pekerjaan.
 */
class JobController extends Controller
{
    public function index(): void
    {
        $type = (string) $this->input('type', '');
        if ($type !== '' && !isset(JobVacancy::EMPLOYMENT_TYPES[$type])) {
            $type = '';
        }

        $vacancies = JobVacancy::published();
        if ($type !== '') {
            $vacancies = array_values(array_filter($vacancies, fn($v) => $v['employment_type'] === $type));
        }

        $counts = [];
        foreach (JobVacancy::published() as $v) {
            $counts[$v['employment_type']] = ($counts[$v['employment_type']] ?? 0) + 1;
        }

        $this->view('job.index', [
            'vacancies'   => $vacancies,
            'activeType'  => $type,
            'counts'      => $counts,
            'totalOpen'   => JobVacancy::countOpen(),
            'typeIcons'   => [
                'full_time'  => 'fa-solid fa-user-briefcase',
                'part_time'  => 'fa-solid fa-business-time',
                'contract'   => 'fa-solid fa-file-signature',
                'freelance'  => 'fa-solid fa-laptop-code',
                'internship' => 'fa-solid fa-graduation-cap',
            ],
            'pageTitle'   => 'Karier',
            'pageDescription' => 'Bergabunglah bersama NurtureFor Growth. Lihat lowongan kerja terbaru untuk posisi psikolog dan tenaga pendukung di klinik psikologi NFG.',
        ]);
    }

    public function show(array $params): void
    {
        $job = JobVacancy::publishedBySlug((string) ($params['slug'] ?? ''));
        if (!$job) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $related = array_values(array_filter(
            JobVacancy::published(),
            fn($v) => (int) $v['id'] !== (int) $job['id']
        ));
        $related = array_slice($related, 0, 3);

        $this->view('job.show', [
            'job'         => $job,
            'related'     => $related,
            'pageTitle'   => 'Lowongan ' . $job['title'],
            'pageDescription' => $job['short_description'] ?: mb_substr(strip_tags((string) $job['description']), 0, 150),
        ]);
    }
}
