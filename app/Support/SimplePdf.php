<?php
namespace App\Support;

/**
 * Penulis PDF minimal untuk laporan tabular (tanpa dependensi eksternal).
 * Core font Helvetica (WinAnsi), A4 otomatis landscape bila kolom >= 7.
 * Fitur: judul + subjudul, header tabel berulang tiap halaman, zebra stripe,
 * perataan kanan otomatis untuk kolom angka/mata uang, nomor halaman.
 */
class SimplePdf
{
    private array $pages = [];
    private array $colWidths = [];
    private array $aligns = [];
    private float $pageW = 841.89; // A4 landscape default
    private float $pageH = 595.28;
    private const MARGIN = 30.0;
    private const FS = 8.0;        // ukuran font isi
    private const PAD = 3.5;       // padding sel horizontal
    private const ROW_PAD = 3.0;   // padding vertikal baris

    public static function download(string $filename, string $title, string $subtitle, array $header, array $rows, ?array $aligns = null): void
    {
        $pdf = new self();
        $pdf->build($title, $subtitle, $header, $rows, $aligns);
        $pdf->output($filename);
    }

    private function build(string $title, string $subtitle, array $header, array $rows, ?array $aligns): void
    {
        $n = count($header);
        if ($n < 7) { // portrait untuk tabel ramping
            $this->pageW = 595.28;
            $this->pageH = 841.89;
        }
        $avail = $this->pageW - 2 * self::MARGIN;

        // Lebar kolom proporsional terhadap panjang konten maksimum
        $lens = array_fill(0, $n, 6.0);
        foreach (array_merge([$header], $rows) as $row) {
            foreach ((array) $row as $i => $v) {
                if ($i >= $n) continue;
                $len = min(mb_strlen((string) $v) + 4, 60);
                if ($len > $lens[$i]) $lens[$i] = $len;
            }
        }
        $total = array_sum($lens);
        $this->colWidths = array_map(fn($l) => max(34.0, $l / $total * $avail), $lens);

        // Perataan: kanan bila mayoritas nilai numerik/Rp; bisa dipaksa via $aligns
        for ($i = 0; $i < $n; $i++) {
            if ($aligns && isset($aligns[$i])) { $this->aligns[$i] = $aligns[$i]; continue; }
            $num = 0; $cnt = 0;
            foreach ($rows as $row) {
                $v = trim((string) ($row[$i] ?? ''));
                if ($v === '') continue;
                $cnt++;
                if (preg_match('/^(Rp\s?)?[\d.,]+$/', $v)) $num++;
            }
            $this->aligns[$i] = ($cnt > 0 && $num / $cnt >= 0.8) ? 'R' : 'L';
        }

        $lineH = self::FS * 1.3;
        $y = $this->pageH - self::MARGIN;

        $buf = $this->startPage();
        $buf .= $this->text(self::MARGIN, $y, $title, 'F2', 14.0, [0.12, 0.12, 0.12]);
        $y -= 15;
        $buf .= $this->text(self::MARGIN, $y, $subtitle, 'F1', 9.0, [0.45, 0.45, 0.45]);
        $y -= 16;
        $buf .= $this->tableHeader($y, $header);
        $y -= $lineH + 2 * self::ROW_PAD;

        foreach ($rows as $idx => $row) {
            $cells = []; $maxLines = 1;
            foreach ((array) $row as $i => $v) {
                if ($i >= $n) continue;
                $lines = $this->wrap((string) $v, $this->colWidths[$i] - 2 * self::PAD, self::FS);
                $cells[$i] = $lines;
                $maxLines = max($maxLines, count($lines));
            }
            $rowH = $maxLines * $lineH + 2 * self::ROW_PAD;

            if ($y - $rowH < self::MARGIN + 18) { // pindah halaman, ulangi header
                $this->pages[] = $buf . $this->footer();
                $buf = $this->startPage();
                $y = $this->pageH - self::MARGIN;
                $buf .= $this->tableHeader($y, $header);
                $y -= $lineH + 2 * self::ROW_PAD;
            }

            if ($idx % 2 === 1) { // zebra stripe
                $buf .= sprintf("0.955 0.965 0.97 rg %.2f %.2f %.2f %.2f re f\n",
                    self::MARGIN, $y - $rowH, array_sum($this->colWidths), $rowH);
            }

            $ty = $y - self::ROW_PAD - self::FS;
            $xLeft = self::MARGIN;
            foreach ($cells as $i => $lines) {
                $x = $this->aligns[$i] === 'R'
                    ? $xLeft + $this->colWidths[$i] - self::PAD
                    : $xLeft + self::PAD;
                foreach ($lines as $li => $ln) {
                    $buf .= $this->text($x, $ty - $li * $lineH, $ln, 'F1', self::FS, [0.13, 0.13, 0.13], $this->aligns[$i]);
                }
                $xLeft += $this->colWidths[$i];
            }

            // Garis pemisah baris tipis
            $buf .= sprintf("0.4 w 0.88 0.88 0.90 RG %.2f %.2f m %.2f %.2f l S\n",
                self::MARGIN, $y - $rowH, self::MARGIN + array_sum($this->colWidths), $y - $rowH);
            $y -= $rowH;
        }

        $this->pages[] = $buf . $this->footer();
    }

    /** Word-wrap sederhana dengan estimasi lebar karakter Helvetica */
    private function wrap(string $text, float $maxW, float $fs): array
    {
        $maxCh = max(3, (int) floor($maxW / ($fs * 0.52)));
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = []; $cur = '';
        foreach ($words as $w) {
            while (mb_strlen($w) > $maxCh) { // kata sangat panjang dipotong
                $lines[] = mb_substr(($cur !== '' ? $cur . ' ' : '') . $w, 0, $maxCh);
                $w = mb_substr($w, $maxCh);
                $cur = '';
            }
            $cand = $cur === '' ? $w : $cur . ' ' . $w;
            if (mb_strlen($cand) <= $maxCh) { $cur = $cand; }
            else { if ($cur !== '') $lines[] = $cur; $cur = $w; }
        }
        if ($cur !== '') $lines[] = $cur;
        return $lines ?: [''];
    }

    private function startPage(): string
    {
        return sprintf("0.5 w 0.92 0.93 0.95 RG %.2f %.2f %.2f %.2f re S\n",
            self::MARGIN, self::MARGIN, $this->pageW - 2 * self::MARGIN, $this->pageH - 2 * self::MARGIN - 40);
    }

    private function tableHeader(float $y, array $header): string
    {
        $h = self::FS * 1.3 + 2 * self::ROW_PAD;
        $buf = sprintf("0.12 0.42 0.36 rg %.2f %.2f %.2f %.2f re f\n",
            self::MARGIN, $y - $h, array_sum($this->colWidths), $h); // teal gelap
        $x = self::MARGIN;
        foreach (array_values($header) as $i => $t) {
            $tx = $this->aligns[$i] === 'R' ? $x + $this->colWidths[$i] - self::PAD : $x + self::PAD;
            $buf .= $this->text($tx, $y - self::ROW_PAD - self::FS, (string) $t, 'F2', self::FS, [1, 1, 1], $this->aligns[$i]);
            $x += $this->colWidths[$i];
        }
        return $buf;
    }

    private function text(float $x, float $y, string $txt, string $font, float $size, array $rgb, string $align = 'L'): string
    {
        return sprintf("BT %s %.1f Tf %.3f %.3f %.3f rg %.2f %.2f Td (%s) Tj ET\n",
            $font, $size, $rgb[0], $rgb[1], $rgb[2], $x, $y, $this->enc($txt));
    }

    private function footer(): string
    {
        $label = 'Dicetak ' . date('d/m/Y H:i') . ' - NFFG';
        $rightX = $this->pageW - self::MARGIN - strlen($label) * 4.2;
        $label2 = 'Halaman ' . count($this->pages);
        return sprintf("BT /F1 7.5 Tf 0.55 0.55 0.55 rg %.2f %.2f Td (%s) Tj ET BT /F1 7.5 Tf 0.55 0.55 0.55 rg %.2f %.2f Td (%s) Tj ET\n",
            self::MARGIN, self::MARGIN - 14, $this->enc($label),
            $rightX, self::MARGIN - 14, $this->enc($label2));
    }

    /** Encode ke CP1252 + escape sesuai sintaks string PDF */
    private function enc(string $s): string
    {
        $s = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
        $conv = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $s);
        return $conv === false ? preg_replace('/[^\x20-\x7E]/', '?', $s) : $conv;
    }

    /**
     * Serialisasi PDF dengan penomoran objek deterministik:
     * 1=Catalog, 2=Pages, 3=F1(Helvetica), 4=F2(Helvetica-Bold),
     * lalu per halaman: [5,6], [7,8], ... (Page object, Content stream).
     */
    private function output(string $filename): void
    {
        $pw = sprintf('%.2f', $this->pageW);
        $ph = sprintf('%.2f', $this->pageH);

        $defs = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Count ' . count($this->pages) . ' /Kids [' .
                 implode(' ', array_map(fn($i) => (5 + 2 * $i) . ' 0 R', array_keys($this->pages))) . '] >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];
        foreach ($this->pages as $i => $content) {
            $pid = 5 + 2 * $i; $cid = 6 + 2 * $i;
            $defs[$pid] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pw} {$ph}] " .
                          "/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$cid} 0 R >>";
            $defs[$cid] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        }

        ksort($defs);
        $body = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($defs as $id => $def) {
            $offsets[$id] = strlen($body);
            $body .= "{$id} 0 obj\n{$def}\nendobj\n";
        }

        $xrefPos = strlen($body);
        $count = count($defs) + 1;
        $body .= "xref\n0 {$count}\n0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $body .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $body .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        header_remove();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($body));
        echo $body;
        exit;
    }
}
