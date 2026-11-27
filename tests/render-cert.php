<?php
declare(strict_types=1);

// Render sample certificates for a visual check (no database needed).
// Run: & 'C:\xampp 8.2\php\php.exe' tests/render-cert.php <outdir>
// Writes cert-php.pdf (short name) and cert-php-long.pdf (name long enough for two lines).

require __DIR__ . '/../src/bootstrap.php';

$out = $argv[1] ?? '';
if ($out === '' || !is_dir($out)) {
    fwrite(STDERR, "Usage: php tests/render-cert.php <existing output folder>\n");
    exit(1);
}

$samples = [
    'cert-php.pdf'      => ['Ali', 'Ahmad'],
    'cert-php-long.pdf' => ['Siti Nur Aisyah Humaira Binti', 'Mohamad Abdul Rahman Shahrul Nizam Al-Haj Ismail Iskandar Zulkarnain Tengku Sulaiman'],
];
foreach ($samples as $file => [$first, $last]) {
    $pdf = render_certificate_pdf([
        'name' => $first . ' ' . $last, 'course_title' => Course::load()->title(),
        'date_text' => format_date_ms('2026-11-23 10:00:00'), 'code' => 'NADI-0123456789', 'issuer' => 'NADI',
    ]);
    file_put_contents($out . DIRECTORY_SEPARATOR . $file, $pdf);
    echo "wrote $file\n";
}
