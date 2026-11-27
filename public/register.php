<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$old = [];
$errors = [];
$remember = false;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_verify();
    $remember = ($_POST['remember'] ?? '') === '1';
    [$clean, $errors] = validate_registration($_POST);
    if (empty($errors)) {
        // Registering again with an e-mail that already exists resumes that learner instead of
        // creating a duplicate. The existing details (and the name on the certificate) are kept.
        $existing = learner_find_for_resume($clean['email']);
        if ($existing !== null) {
            if (!resume_attempt_allowed($clean['email'])) {
                http_response_code(429);
                header('Retry-After: ' . (RESUME_WINDOW_MINUTES * 60));
                render('register', [
                    'title'       => 'Pendaftaran',
                    'old'         => $clean,
                    'errors'      => [],
                    'remember'    => $remember,
                    'formMessage' => 'Terlalu banyak cubaan Sambung Semula. Sila tunggu '
                        . RESUME_WINDOW_MINUTES . ' minit dan cuba lagi.',
                ]);
                exit;
            }
            resume_login($existing, $remember);
            flash_set('E-mel ini sudah berdaftar, jadi kemajuan anda yang sedia ada telah disambung semula. '
                . 'Nama pada sijil: ' . learner_name($existing) . '.');
            redirect('pilih.php');
        }
        $rawToken = bin2hex(random_bytes(32));
        $id = learner_create($clean, hash('sha256', $rawToken));
        login_learner($id, $rawToken, $remember);
        // New learners choose E-Learning or Self-Learning first.
        redirect('pilih.php');
    }
    $old = $clean;
    http_response_code(422);
}

render('register', [
    'title'    => 'Pendaftaran',
    'old'      => $old,
    'errors'   => $errors,
    'remember' => $remember,
]);
