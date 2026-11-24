<?php
declare(strict_types=1);

// "Sambung Semula": a learner who registered earlier (on any device) continues by entering their e-mail.
require __DIR__ . '/../src/bootstrap.php';

$email = '';
$error = null;
$notFound = false;
$remember = false;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_verify();
    $remember = ($_POST['remember'] ?? '') === '1';
    [$email, $error] = validate_resume_email($_POST);
    if ($error === null) {
        if (!resume_attempt_allowed($email)) {
            http_response_code(429);
            header('Retry-After: ' . (RESUME_WINDOW_MINUTES * 60));
            $error = 'Terlalu banyak cubaan Sambung Semula. Sila tunggu ' . RESUME_WINDOW_MINUTES
                . ' minit dan cuba lagi.';
        } else {
            $learner = learner_find_for_resume($email);
            if ($learner !== null) {
                resume_login($learner, $remember);
                flash_set('Selamat kembali, ' . $learner['first_name'] . ' ' . $learner['last_name']
                    . '. Kemajuan anda telah disambung semula.');
                redirect('course.php');
            }
            $notFound = true;
            http_response_code(404);
        }
    } else {
        http_response_code(422);
    }
}

render('resume', [
    'title'    => 'Sambung Semula',
    'email'    => $email,
    'error'    => $error,
    'notFound' => $notFound,
    'remember' => $remember,
]);
