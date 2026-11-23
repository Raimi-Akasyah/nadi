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

/**
 * Render the certificate as PDF bytes.
 *
 * @param array $d first_name, last_name, course_title, date_text, code, issuer
 */
function render_certificate_pdf(array $d): string
{
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->SetCompression(false);
    $pdf->SetTitle(pdf_text('Sijil ' . $d['code']));
    $pdf->SetAuthor(pdf_text((string)$d['issuer']));
    $pdf->SetAutoPageBreak(false);
    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();

    $w = $pdf->GetPageWidth();
    $h = $pdf->GetPageHeight();
    $inner = $w - 40;

    // Double border.
    $pdf->SetDrawColor(31, 78, 140);
    $pdf->SetLineWidth(1.5);
    $pdf->Rect(10, 10, $w - 20, $h - 20);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(14, 14, $w - 28, $h - 28);

    // Text logo (placeholder until a real logo is supplied).
    $pdf->SetTextColor(31, 78, 140);
    $pdf->SetFont('Helvetica', 'B', 22);
    $pdf->SetXY(20, 26);
    $pdf->Cell($inner, 10, pdf_text((string)$d['issuer']), 0, 1, 'C');

    $pdf->SetTextColor(29, 36, 51);
    $pdf->SetFont('Helvetica', 'B', 30);
    $pdf->SetXY(20, 48);
    $pdf->Cell($inner, 14, pdf_text('SIJIL PENYELESAIAN'), 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 14);
    $pdf->SetXY(20, 72);
    $pdf->Cell($inner, 8, pdf_text('Dengan ini disahkan bahawa'), 0, 1, 'C');

    $pdf->SetFont('Times', 'B', 32);
    $pdf->SetXY(20, 86);
    $pdf->Cell($inner, 16, pdf_text($d['first_name'] . ' ' . $d['last_name']), 0, 1, 'C');

    $pdf->SetDrawColor(180, 180, 180);
    $pdf->SetLineWidth(0.3);
    $pdf->Line($w / 2 - 70, 104, $w / 2 + 70, 104);

    $pdf->SetFont('Helvetica', '', 14);
    $pdf->SetXY(20, 112);
    $pdf->Cell($inner, 8, pdf_text('telah berjaya menamatkan kursus'), 0, 1, 'C');

    $pdf->SetFont('Helvetica', 'B', 20);
    $pdf->SetXY(20, 124);
    $pdf->Cell($inner, 12, pdf_text((string)$d['course_title']), 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 12);
    $pdf->SetXY(20, 156);
    $pdf->Cell($inner, 7, pdf_text('Tarikh: ' . $d['date_text']), 0, 1, 'C');
    $pdf->SetXY(20, 164);
    $pdf->Cell($inner, 7, pdf_text('No. Sijil: ' . $d['code']), 0, 1, 'C');

    return $pdf->Output('S');
}
