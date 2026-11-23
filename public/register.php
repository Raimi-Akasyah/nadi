<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$old = [];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_verify();
    [$clean, $errors] = validate_registration($_POST);
    if (empty($errors)) {
        $rawToken = bin2hex(random_bytes(32));
        $id = learner_create($clean, hash('sha256', $rawToken));
        login_learner($id, $rawToken);
        redirect('course.php');
    }
    $old = $clean;
    http_response_code(422);
}

render('register', [
    'title'  => 'Pendaftaran',
    'old'    => $old,
    'errors' => $errors,
]);
