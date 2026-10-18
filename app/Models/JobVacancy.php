<?php
namespace App\Models;
use Core\Model;

class JobVacancy extends Model
{
    protected static string $table = 'job_vacancies';

    public const EMPLOYMENT_TYPES = [
        'full_time'  => 'Full-Time',
        'part_time'  => 'Part-Time',
        'contract'   => 'Kontrak',
        'freelance'  => 'Freelance',
        'internship' => 'Magang',
    ];

    public const WORK_MODES = [
        'onsite' => 'On-site',
        'hybrid' => 'Hybrid',
        'remote' => 'Remote',
    ];

    public static function published(): array
    {
        return self::raw(
            "SELECT * FROM job_vacancies
             WHERE is_published = 1 AND (deadline_date IS NULL OR deadline_date >= CURDATE())
             ORDER BY sort_order ASC, created_at DESC"
        );
    }

    public static function publishedBySlug(string $slug): ?array
    {
        return self::rawOne(
            "SELECT * FROM job_vacancies
             WHERE slug = ? AND is_published = 1 AND (deadline_date IS NULL OR deadline_date >= CURDATE())
             LIMIT 1",
            [$slug]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::rawOne("SELECT * FROM job_vacancies WHERE slug = ? LIMIT 1", [$slug]);
    }

    public static function countOpen(): int
    {
        $row = self::rawOne(
            "SELECT COUNT(*) c FROM job_vacancies
             WHERE is_published = 1 AND (deadline_date IS NULL OR deadline_date >= CURDATE())"
        );
        return (int) ($row['c'] ?? 0);
    }

    /** Slug unik dari judul. */
    public static function uniqueSlug(string $title, int $ignoreId = 0): string
    {
        $base = self::slugify($title) ?: 'lowongan';
        $slug = $base;
        $i = 2;
        while (self::rawOne("SELECT id FROM job_vacancies WHERE slug = ? AND id != ? LIMIT 1", [$slug, $ignoreId])) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = str_replace(['&', '/', '\\'], [' dan ', '-', '-'], $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    public static function isOpen(array $vacancy): bool
    {
        if (!(int) $vacancy['is_published']) return false;
        if (!empty($vacancy['deadline_date']) && $vacancy['deadline_date'] < date('Y-m-d')) return false;
        return true;
    }

    public static function salaryLabel(array $v): ?string
    {
        if (!(int) $v['salary_visible']) return null;
        $min = $v['salary_min'] ? (int) $v['salary_min'] : null;
        $max = $v['salary_max'] ? (int) $v['salary_max'] : null;
        if (!$min && !$max) return null;
        if ($min && $max) return 'Rp' . number_format($min / 1000000, $min % 1000000 ? 1 : 0) . ' jt - Rp' . number_format($max / 1000000, $max % 1000000 ? 1 : 0) . ' jt';
        $val = $max ?: $min;
        return 'Rp' . number_format($val, 0, ',', '.');
    }
}
