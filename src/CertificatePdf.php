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
 * Render the certificate (SIJIL PENYERTAAN) as PDF bytes.
 * Wording and images come from content/certificate.php.
 *
 * @param array $d first_name, last_name, course_title, date_text, code, issuer
 */
function render_certificate_pdf(array $d): string
{
    $cert = require APP_ROOT . '/content/certificate.php';
    $img = static fn (string $path): string => APP_ROOT . '/public/' . $path;

    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->SetCompression(false);
    $pdf->SetTitle(pdf_text('Sijil ' . $d['code']));
    $pdf->SetAuthor(pdf_text((string)$d['issuer']));
    $pdf->SetAutoPageBreak(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->AddPage();

    $w = $pdf->GetPageWidth();
    $h = $pdf->GetPageHeight();
    $blue = [16, 62, 110];
    $ink = [17, 17, 17];

    $pdf->Image($img($cert['background']), 0, 0, $w, $h);

    // Logos: 18 mm high, centred.
    $logos = $img($cert['logos']);
    [$lw, $lh] = getimagesize($logos);
    $logoW = 18 * $lw / $lh;
    $pdf->Image($logos, ($w - $logoW) / 2, 9, $logoW, 18);

    $pdf->SetTextColor(...$ink);
    pdf_line($pdf, 34, 14, $cert['title'], 'B', 34);
    pdf_line($pdf, 54, 6, $cert['intro'], '', 12);

    // Learner name: shrink until it fits 240 mm.
    $name = pdf_text($d['first_name'] . ' ' . $d['last_name']);
    $size = 26;
    $pdf->SetFont('Helvetica', 'B', $size);
    while ($size > 14 && $pdf->GetStringWidth($name) > 240) {
        $size--;
        $pdf->SetFont('Helvetica', 'B', $size);
    }
    $pdf->SetTextColor(...$blue);
    $pdf->SetXY(0, 62);
    $pdf->Cell($w, 12, $name, 0, 0, 'C');

    $pdf->SetTextColor(...$ink);
    pdf_line($pdf, 88, 6, $cert['completed'], '', 12);
    $pdf->SetTextColor(...$blue);
    pdf_line($pdf, 96, 9, $cert['program'], 'B', 18);
    $pdf->SetTextColor(...$ink);
    pdf_line($pdf, 114, 6, $cert['focus'], 'B', 11);

    // Summary line: normal, bold, normal pieces measured and printed side by side.
    $styles = ['', 'B', ''];
    $widths = [];
    foreach ($cert['summary'] as $i => $part) {
        $pdf->SetFont('Helvetica', $styles[$i % 3], 10.5);
        $widths[$i] = $pdf->GetStringWidth(pdf_text($part));
    }
    $pdf->SetXY(($w - array_sum($widths)) / 2, 122);
    foreach ($cert['summary'] as $i => $part) {
        $pdf->SetFont('Helvetica', $styles[$i % 3], 10.5);
        $pdf->Cell($widths[$i], 5, pdf_text($part), 0, 0, 'L');
    }
    foreach ($cert['modules'] as $i => $line) {
        pdf_line($pdf, 128 + 6 * $i, 5, $line, '', 10.5);
    }

    // Signature.
    $pdf->SetDrawColor(...$ink);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(116, 178, 181, 178);
    pdf_line($pdf, 180, 5, $cert['signatory']['name'], 'B', 10);
    pdf_line($pdf, 185, 5, $cert['signatory']['title'], '', 10);
    pdf_line($pdf, 190, 5, $cert['signatory']['org'], '', 10);

    $pdf->Image($img($cert['badge']), 212, 152, 28);

    pdf_line($pdf, 186, 5, $cert['date_label'] . ': ' . $d['date_text'], '', 9, 18);
    pdf_line($pdf, 191, 5, $cert['code_label'] . ': ' . $d['code'], '', 9, 18);

    return $pdf->Output('S');
}
