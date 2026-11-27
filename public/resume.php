<?php
declare(strict_types=1);

// "Sambung Semula": a learner who registered earlier (on any device) continues by entering their full name or
// their e-mail.
require __DIR__ . '/../src/bootstrap.php';

$login = '';
$error = null;
$notFound = false;
$remember = false;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_verify();
    $remember = ($_POST['remember'] ?? '') === '1';
    [$kind, $login, $error] = validate_resume_login($_POST);
    if ($error === null) {
        if (!resume_attempt_allowed($login)) {
            http_response_code(429);
            header('Retry-After: ' . (RESUME_WINDOW_MINUTES * 60));
            $error = 'Terlalu banyak cubaan Sambung Semula. Sila tunggu ' . RESUME_WINDOW_MINUTES
                . ' minit dan cuba lagi.';
        } else {
            $learner = null;
            if ($kind === 'email') {
                $learner = learner_find_for_resume($login);
            } else {
                $match = learner_find_for_resume_by_name($login);
                $learner = $match['learner'];
                if ($match['ambiguous']) {
                    // Two different people share this name: resuming either one could show someone else's
                    // progress and certificate, so ask for the e-mail.
                    http_response_code(409);
                    $error = 'Lebih daripada seorang peserta berdaftar dengan nama ini. '
                        . 'Sila Sambung Semula dengan e-mel yang anda gunakan semasa mendaftar.';
                }
            }
            if ($learner !== null) {
                resume_login($learner, $remember);
                flash_set('Selamat kembali, ' . learner_name($learner) . '. Kemajuan anda telah disambung semula.');
                redirect('pilih.php');
            }
            if ($error === null) {
                $notFound = true;
                http_response_code(404);
            }
        }
    } else {
        http_response_code(422);
    }
}

render('resume', [
    'title'    => 'Sambung Semula',
    'login'    => $login,
    'error'    => $error,
    'notFound' => $notFound,
    'remember' => $remember,
]);
