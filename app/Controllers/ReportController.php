<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Middleware;
use App\Support\SimpleXlsx;
use App\Support\SimplePdf;
use App\Models\AssessmentResult;
use App\Models\Mood;
use App\Models\Consultation;
use App\Models\ReportExport;

/**
 * Pusat laporan: assessment, konsultasi, mood, dan pendapatan (revenue).
 * Setiap laporan dapat diekspor ke CSV, Excel (.xlsx), dan PDF.
 */
class ReportController extends Controller
{
    /** Tipe laporan yang valid -> label judul */
    private const TYPES = [
        'assessment'      => 'Laporan Assessment',
        'consultation'    => 'Laporan Konsultasi',
        'mood'            => 'Laporan Mood',
        'revenue'         => 'Laporan Pendapatan (Detail)',
        'revenue_daily'   => 'Rekap Pendapatan Harian',
        'revenue_monthly' => 'Rekap Pendapatan Bulanan',
        'revenue_yearly'  => 'Rekap Pendapatan Tahunan',
    ];

    public function index(): void
    {
        Middleware::permission('report.view');
        $this->view('report.index', ['rev' => $this->revenueStats()]);
    }

    // ===== Rute CSV lama tetap berfungsi =====
    public function exportAssessmentCsv(): void { $this->export('assessment', 'csv'); }
    public function exportConsultationCsv(): void { $this->export('consultation', 'csv'); }
    public function exportMoodCsv(): void { $this->export('mood', 'csv'); }

    /**
     * Ekspor seragam: /report/export/{type}/{format}
     * type = assessment|consultation|mood|revenue|revenue_daily|revenue_monthly|revenue_yearly
     * format = csv|xlsx|pdf
     */
    public function export(string $type, string $format): void
    {
        Middleware::permission('report.view');
        if (!isset(self::TYPES[$type])) {
            http_response_code(404);
            echo 'Laporan tidak dikenali.';
            exit;
        }
        $r = $this->buildReport($type);

        ReportExport::create([
            'requested_by' => Auth::id(),
            'report_type'  => $type,
            'format'       => $format,
            'file_path'    => null,
            'filters_json' => null,
        ]);
        audit('export', 'report', "Export {$format}: {$type}");

        $stamp = date('Ymd-His');
        if ($format === 'xlsx') {
            SimpleXlsx::download("{$type}-{$stamp}.xlsx", $r['title'], $r['header'], $r['rows']);
        } elseif ($format === 'pdf') {
            SimplePdf::download("{$type}-{$stamp}.pdf", $r['title'], $this->pdfSubtitle($r['title']), $r['header'], $r['rows'], $r['aligns'] ?? null);
        } else {
            $this->streamCsv("{$type}", $r['header'], $r['rows']);
        }
    }

    // ================================================================
    //  Perakitan data per tipe laporan
    // ================================================================
    private function buildReport(string $type): array
    {
        return match ($type) {
            'assessment' => $this->reportAssessment(),
            'consultation' => $this->reportConsultation(),
            'mood' => $this->reportMood(),
            'revenue' => $this->reportRevenueDetail(),
            default => $this->reportRevenueRecap(substr($type, strlen('revenue_'))), // daily|monthly|yearly
        };
    }

    private function reportAssessment(): array
    {
        $sql = "SELECT ar.created_at, u.name AS patient_name, a.title AS assessment_title,
                       ar.total_score, sr.category_label, sr.is_high_risk
                FROM assessment_results ar
                JOIN patients p ON p.id = ar.patient_id
                JOIN users u ON u.id = p.user_id
                JOIN assessments a ON a.id = ar.assessment_id
                LEFT JOIN assessment_score_ranges sr ON sr.id = ar.score_range_id";
        [$sql, $params] = $this->scopeByProvider($sql, 'p');
        $sql .= " ORDER BY ar.created_at DESC";
        $rows = AssessmentResult::raw($sql, $params);
        return [
            'title' => self::TYPES['assessment'],
            'header' => ['Tanggal', 'Client', 'Assessment', 'Skor', 'Kategori', 'Risiko Tinggi'],
            'rows' => array_map(fn($r) => [
                $this->dt($r['created_at']), $r['patient_name'], $r['assessment_title'],
                (string) $r['total_score'], $r['category_label'] ?: '-', $r['is_high_risk'] ? 'Ya' : 'Tidak',
            ], $rows),
        ];
    }

    private function reportConsultation(): array
    {
        $sql = "SELECT c.schedule_at, u.name AS patient_name, pu.name AS provider_name, c.method, c.status
                FROM consultations c
                JOIN patients p ON p.id = c.patient_id JOIN users u ON u.id = p.user_id
                JOIN users pu ON pu.id = c.provider_id";
        [$sql, $params] = $this->scopeConsultation($sql);
        $sql .= " ORDER BY c.schedule_at DESC";
        $rows = Consultation::raw($sql, $params);
        return [
            'title' => self::TYPES['consultation'],
            'header' => ['Jadwal', 'Client', 'Psikolog/Psikiater', 'Metode', 'Status'],
            'rows' => array_map(fn($r) => [
                $this->dt($r['schedule_at']), $r['patient_name'], $r['provider_name'],
                ucfirst($r['method']), ucfirst($r['status']),
            ], $rows),
        ];
    }

    private function reportMood(): array
    {
        $sql = "SELECT m.mood_date, u.name AS patient_name, m.mood_scale, m.cause, m.activity
                FROM moods m JOIN patients p ON p.id = m.patient_id JOIN users u ON u.id = p.user_id";
        [$sql, $params] = $this->scopeByProvider($sql, 'p');
        $sql .= " ORDER BY m.mood_date DESC";
        $rows = Mood::raw($sql, $params);
        return [
            'title' => self::TYPES['mood'],
            'header' => ['Tanggal', 'Client', 'Skala Mood', 'Penyebab', 'Aktivitas'],
            'rows' => array_map(fn($r) => [
                $this->dt($r['mood_date']), $r['patient_name'], (string) $r['mood_scale'],
                $r['cause'] ?: '-', $r['activity'] ?: '-',
            ], $rows),
        ];
    }

    private function reportRevenueDetail(): array
    {
        $sql = "SELECT COALESCE(c.payment_verified_at, c.schedule_at) AS paid_date, c.schedule_at,
                       u.name AS patient_name, pu.name AS provider_name, c.method, c.session_type,
                       c.original_price_amount, c.discount_amount, c.price_amount, c.payment_method
                FROM consultations c
                JOIN patients p ON p.id = c.patient_id JOIN users u ON u.id = p.user_id
                JOIN users pu ON pu.id = c.provider_id
                WHERE c.payment_status = 'paid'";
        [$sql, $params] = $this->scopeRevenue($sql);
        $sql .= " ORDER BY paid_date DESC";
        $rows = Consultation::raw($sql, $params);
        return [
            'title' => self::TYPES['revenue'],
            'header' => ['Tanggal Bayar', 'Jadwal Sesi', 'Client', 'Psikolog', 'Metode Sesi',
                         'Harga Asli', 'Diskon', 'Total Dibayar', 'Metode Bayar'],
            'aligns' => ['L','L','L','L','L','R','R','R','L'],
            'rows' => array_map(fn($r) => [
                $this->dt($r['paid_date']), $this->dt($r['schedule_at']),
                $r['patient_name'], $r['provider_name'], ucfirst($r['method']) .
                    ($r['session_type'] ? ' (' . ucfirst(str_replace('_', ' ', $r['session_type'])) . ')' : ''),
                $this->rp($r['original_price_amount']), $this->rp($r['discount_amount']),
                $this->rp($r['price_amount']), $this->pmLabel($r['payment_method']),
            ], $rows),
        ];
    }

    private function reportRevenueRecap(string $period): array
    {
        $groupExpr = match ($period) {
            'monthly' => "DATE_FORMAT(COALESCE(c.payment_verified_at, c.schedule_at), '%Y-%m')",
            'yearly'  => "CAST(YEAR(COALESCE(c.payment_verified_at, c.schedule_at)) AS CHAR)",
            default   => "DATE(COALESCE(c.payment_verified_at, c.schedule_at))",
        };
        $colLabel = match ($period) {
            'monthly' => 'Bulan', 'yearly' => 'Tahun', default => 'Tanggal',
        };

        $sql = "SELECT {$groupExpr} AS periode, COUNT(*) AS sessions,
                       COALESCE(SUM(c.discount_amount),0) AS discount,
                       COALESCE(SUM(c.price_amount),0) AS revenue
                FROM consultations c
                WHERE c.payment_status = 'paid'";
        [$sql, $params] = $this->scopeRevenue($sql);
        $sql .= " GROUP BY periode ORDER BY periode DESC";

        $rows = Consultation::raw($sql, $params);
        return [
            'title' => self::TYPES["revenue_{$period}"] ?? self::TYPES['revenue'],
            'header' => [$colLabel, 'Jumlah Sesi', 'Total Diskon', 'Total Pendapatan'],
            'aligns' => ['L','R','R','R'],
            'rows' => array_map(fn($r) => [
                (string) $r['periode'], (string) $r['sessions'],
                $this->rp($r['discount']), $this->rp($r['revenue']),
            ], $rows),
        ];
    }

    /** Statistik pendapatan untuk halaman /report */
    private function revenueStats(): array
    {
        $base = "FROM consultations c WHERE c.payment_status = 'paid'";
        [$base, $params] = $this->scopeRevenue($base);

        $sum = Consultation::raw(
            "SELECT COUNT(*) AS sessions, COALESCE(SUM(c.price_amount),0) AS revenue,
                    COALESCE(SUM(c.discount_amount),0) AS discount,
                    COALESCE(SUM(CASE WHEN DATE(COALESCE(c.payment_verified_at,c.schedule_at)) = CURDATE() THEN c.price_amount END),0) AS today
             {$base}", $params)[0] ?? null;

        $monthly = Consultation::raw(
            "SELECT DATE_FORMAT(COALESCE(c.payment_verified_at,c.schedule_at), '%Y-%m') AS periode,
                    COUNT(*) AS sessions, COALESCE(SUM(c.discount_amount),0) AS discount,
                    COALESCE(SUM(c.price_amount),0) AS revenue
             {$base} GROUP BY periode ORDER BY periode DESC LIMIT 12", $params);

        $daily = Consultation::raw(
            "SELECT DATE(COALESCE(c.payment_verified_at,c.schedule_at)) AS periode,
                    COUNT(*) AS sessions, COALESCE(SUM(c.discount_amount),0) AS discount,
                    COALESCE(SUM(c.price_amount),0) AS revenue
             {$base} GROUP BY periode ORDER BY periode DESC LIMIT 14", $params);

        $yearly = Consultation::raw(
            "SELECT CAST(YEAR(COALESCE(c.payment_verified_at,c.schedule_at)) AS CHAR) AS periode,
                    COUNT(*) AS sessions, COALESCE(SUM(c.discount_amount),0) AS discount,
                    COALESCE(SUM(c.price_amount),0) AS revenue
             {$base} GROUP BY periode ORDER BY periode DESC LIMIT 5", $params);

        $sessions = (int) ($sum['sessions'] ?? 0);
        $revenue = (float) ($sum['revenue'] ?? 0);
        return [
            'sessions' => $sessions,
            'revenue'  => $revenue,
            'discount' => (float) ($sum['discount'] ?? 0),
            'today'    => (float) ($sum['today'] ?? 0),
            'avg'      => $sessions > 0 ? $revenue / $sessions : 0.0,
            'daily'    => $daily,
            'monthly'  => $monthly,
            'yearly'   => $yearly,
        ];
    }

    // ================================================================
    //  Helper
    // ================================================================
    private function scopeByProvider(string $sql, string $patientAlias): array
    {
        if (in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            $clause = "({$patientAlias}.assigned_psychologist_id = ? OR {$patientAlias}.assigned_psychiatrist_id = ?)";
            $sql .= self::cond($sql) . ' ' . $clause;
            return [$sql, [Auth::id(), Auth::id()]];
        }
        return [$sql, []];
    }

    /** Scope untuk query yang mulai dari tabel consultations */
    private function scopeConsultation(string $sql): array
    {
        if (in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            $sql .= self::cond($sql) . ' c.provider_id = ?';
            return [$sql, [Auth::id()]];
        }
        return [$sql, []];
    }

    /** Menentukan kata kunci: SQL yang sudah punya WHERE memakai AND, selain itu WHERE baru. */
    private static function cond(string $sql): string
    {
        return stripos($sql, ' WHERE ') !== false ? ' AND' : ' WHERE';
    }

    private function scopeRevenue(string $sql): array
    {
        return $this->scopeConsultation($sql);
    }

    private function pdfSubtitle(string $title): string
    {
        $role = Auth::roleName();
        $scope = in_array($role, ['psikolog', 'psikiater'], true) ? ' (data client yang Anda tangani)' : ' (seluruh klinik)';
        return "NFFG - {$title}{$scope} - diekspor " . date('d/m/Y H:i');
    }

    private function dt(?string $v): string
    {
        if (!$v) return '-';
        $ts = strtotime($v);
        return $ts ? date('d/m/Y H:i', $ts) : $v;
    }

    private function rp($v): string
    {
        return 'Rp ' . number_format((float) $v, 0, ',', '.');
    }

    private function pmLabel(?string $pm): string
    {
        return match ($pm) {
            'qris' => 'QRIS',
            null, '' => '-',
            default => ucwords(str_replace(['_', '-'], ' ', $pm)),
        };
    }

    private function streamCsv(string $baseName, array $header, array $rows): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $baseName . '-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM agar Excel membaca karakter Indonesia dgn benar
        fputcsv($out, $header);
        foreach ($rows as $r) { fputcsv($out, array_values((array) $r)); }
        fclose($out);
        exit;
    }
}
