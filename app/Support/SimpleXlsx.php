<?php
namespace App\Support;

/**
 * Penulis file .xlsx minimal (tanpa dependensi eksternal).
 * Cukup untuk laporan tabular: 1 sheet, header tebal, lebar kolom otomatis.
 */
class SimpleXlsx
{
    public static function download(string $filename, string $sheetName, array $header, array $rows): void
    {
        if (!class_exists(\ZipArchive::class)) {
            // Fallback aman bila ekstensi zip nonaktif: CSV dengan BOM
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . preg_replace('/\.xlsx$/', '.csv', $filename) . '"');
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $header);
            foreach ($rows as $r) { fputcsv($out, $r); }
            fclose($out);
            exit;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', self::xmlContentTypes());
        $zip->addFromString('_rels/.rels', self::xmlRels());
        $zip->addFromString('xl/workbook.xml', self::xmlWorkbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::xmlWorkbookRels());
        $zip->addFromString('xl/styles.xml', self::xmlStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::xmlSheet($header, $rows));

        $zip->close();
        readfile($tmp);
        @unlink($tmp);
        exit;
    }

    private static function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function cellValue($v): string
    {
        return is_scalar($v) || $v === null ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE);
    }

    private static function xmlSheet(array $header, array $rows): string
    {
        $cols = count($header);
        $colLetter = function (int $i): string {
            $s = '';
            for ($n = $i; $n >= 0; $n = intdiv($n, 26) - 1) {
                $s = chr(65 + ($n % 26)) . $s;
                if ($n < 26) break;
            }
            return $s;
        };

        // Lebar kolom dari panjang konten maksimum (dibatasi)
        $widths = array_fill(0, $cols, 8.0);
        $all = array_merge([$header], $rows);
        foreach ($all as $row) {
            foreach (array_slice((array) $row, 0, $cols) as $i => $v) {
                $len = min(mb_strlen(self::cellValue($v)) + 2, 48);
                if ($len > $widths[$i]) $widths[$i] = $len;
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
               '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols>';
        foreach ($widths as $i => $w) {
            $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . round($w + 1, 1) . '" customWidth="1"/>';
        }
        $xml .= '</cols><sheetData>';

        // Baris header (style 1 = bold + fill)
        $xml .= '<row r="1">';
        foreach (array_values($header) as $i => $h) {
            $ref = $colLetter($i) . '1';
            $xml .= '<c r="' . $ref . '" s="1" t="inlineStr"><is><t xml:space="preserve">' . self::esc(self::cellValue($h)) . '</t></is></c>';
        }
        $xml .= '</row>';

        foreach ($rows as $rn => $row) {
            $r = $rn + 2;
            $xml .= '<row r="' . $r . '">';
            foreach (array_slice((array) $row, 0, $cols) as $i => $v) {
                $ref = $colLetter($i) . $r;
                $val = self::cellValue($v);
                if ($val !== '' && is_numeric($val) && !preg_match('/^0[0-9]/', $val)) {
                    $xml .= '<c r="' . $ref . '"><v>' . $val . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . self::esc($val) . '</t></is></c>';
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData></worksheet>';
        return $xml;
    }

    private static function xmlStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
'<fonts count="2">' .
'<font><sz val="11"/><name val="Calibri"/></font>' .
'<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>' .
'</fonts>' .
'<fills count="3">' .
'<fill><patternFill patternType="none"/></fill>' .
'<fill><patternFill patternType="gray125"/></fill>' .
'<fill><patternFill patternType="solid"><fgColor rgb="FF1F6F5B"/><bgColor indexed="64"/></patternFill></fill>' .
'</fills>' .
'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>' .
'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
'<cellXfs count="2">' .
'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"><alignment horizontal="center" vertical="center"/></xf>' .
'</cellXfs>' .
'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>' .
'</styleSheet>';
    }

    private static function xmlWorkbook(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
'<sheets><sheet name="' . self::esc(mb_substr($sheetName ?: 'Laporan', 0, 28)) . '" sheetId="1" r:id="rId1"/></sheets>' .
'</workbook>';
    }

    private static function xmlWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
'</Relationships>';
    }

    private static function xmlContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
'<Default Extension="xml" ContentType="application/xml"/>' .
'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
'</Types>';
    }

    private static function xmlRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
'</Relationships>';
    }
}
