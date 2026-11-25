<?php
declare(strict_types=1);

require_once APP_ROOT . '/lib/fpdf/fpdf.php';

/** Convert UTF-8 text to Windows-1252 for FPDF core fonts. */
function pdf_text(string $s): string
{
    $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
    if ($out === false) {
        $out = mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
    }
    return (string)$out;
}

/** Print one line of text centred on the page (or at $x, left-aligned). */
function pdf_line(FPDF $pdf, float $y, float $h, string $text, string $style, float $size, ?float $x = null): void
{
    $pdf->SetFont('Helvetica', $style, $size);
    if ($x === null) {
        $pdf->SetXY(0, $y);
        $pdf->Cell($pdf->GetPageWidth(), $h, pdf_text($text), 0, 0, 'C');
    } else {
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, $h, pdf_text($text), 0, 0, 'L');
    }
}

/**
 * Fit the learner's name into $cfg['max_width'] mm.
 * One line from max_size down to min_size in 1 pt steps; if it still does not fit,
 * two lines split at the space nearest the middle (or the middle character), shrunk
 * together from min_size in 0.5 pt steps down to 4 pt.
 * The same algorithm is in docs/assets/js/app.js (nameLayout).
 *
 * @param callable(string, float): float $measure width in mm of $text at $size pt
 * @return array{size: float, lines: string[]}
 */
function certificate_name_layout(callable $measure, string $name, array $cfg): array
{
    $max = (float)$cfg['max_width'];
    for ($size = (float)$cfg['max_size']; $size >= (float)$cfg['min_size']; $size -= 1) {
        if ($measure($name, $size) <= $max) {
            return ['size' => $size, 'lines' => [$name]];
        }
    }

    $chars = mb_str_split($name);
    $mid = intdiv(count($chars), 2);
    $split = null;
    foreach ($chars as $i => $ch) {
        if ($ch === ' ' && ($split === null || abs($i - $mid) < abs($split - $mid))) {
            $split = $i;
        }
    }
    if ($split !== null) {
        $lines = [implode('', array_slice($chars, 0, $split)), implode('', array_slice($chars, $split + 1))];
    } else {
        $lines = [implode('', array_slice($chars, 0, $mid)), implode('', array_slice($chars, $mid))];
    }

    $size = (float)$cfg['min_size'];
    while ($size > 4 && max($measure($lines[0], $size), $measure($lines[1], $size)) > $max) {
        $size -= 0.5;
    }
    return ['size' => $size, 'lines' => $lines];
}

/**
 * Render the certificate as PDF bytes: the template picture fills the page, the
 * placeholder is covered, and the name, date and certificate number are printed.
 * Template and positions come from content/certificate.php.
 *
 * @param array $d first_name, last_name, course_title, date_text, code, issuer
 */
function render_certificate_pdf(array $d): string
{
    $cert = require APP_ROOT . '/content/certificate.php';

    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->SetCompression(false);
    $pdf->SetTitle(pdf_text('Sijil ' . $d['code']));
    $pdf->SetAuthor(pdf_text((string)$d['issuer']));
    $pdf->SetAutoPageBreak(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->AddPage();

    $pdf->Image(APP_ROOT . '/public/' . $cert['template'], 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());

    $n = $cert['name'];
    $pdf->SetFillColor(...$n['patch_color']);
    $pdf->Rect($n['patch'][0], $n['patch'][1], $n['patch'][2], $n['patch'][3], 'F');

    $measure = static function (string $text, float $size) use ($pdf): float {
        $pdf->SetFont('Helvetica', 'B', $size);
        return $pdf->GetStringWidth(pdf_text($text));
    };
    $layout = certificate_name_layout($measure, $d['first_name'] . ' ' . $d['last_name'], $n);
    $lineH = $layout['size'] * 0.3528 * 1.15;
    $top = $n['center_y'] - count($layout['lines']) * $lineH / 2;
    $pdf->SetTextColor(...$n['color']);
    foreach ($layout['lines'] as $i => $line) {
        pdf_line($pdf, $top + $i * $lineH, $lineH, $line, 'B', $layout['size']);
    }

    $t = $cert['details'];
    $pdf->SetTextColor(...$t['color']);
    pdf_line($pdf, $t['y'], $t['line_height'], $cert['date_label'] . ': ' . $d['date_text'], '', $t['size'], $t['x']);
    pdf_line($pdf, $t['y'] + $t['line_height'], $t['line_height'], $cert['code_label'] . ': ' . $d['code'], '', $t['size'], $t['x']);

    return $pdf->Output('S');
}
