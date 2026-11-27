<?php
declare(strict_types=1);

// Panel admin: course figures, the learner list and CSV export, for the organisers. On only when an admin password
// hash is configured (src/admin.php); otherwise this page answers 404.
require __DIR__ . '/../src/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

if (!admin_enabled()) {
    abort(404, 'Halaman tidak dijumpai.');
}

$loginError = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_verify();
    if (($_POST['action'] ?? '') === 'logout') {
        admin_sign_out();
        redirect('admin.php');
    }
    $password = $_POST['password'] ?? '';
    $result = admin_sign_in(is_string($password) ? $password : '');
    if ($result === 'ok') {
        redirect('admin.php');
    }
    http_response_code($result === 'locked' ? 429 : 401);
    $loginError = $result === 'locked'
        ? 'Terlalu banyak cubaan log masuk. Sila tunggu ' . ADMIN_LOGIN_WINDOW_MINUTES . ' minit dan cuba lagi.'
        : 'Kata laluan salah.';
}

if (!admin_signed_in()) {
    render('admin-login', ['title' => 'Log Masuk Admin', 'error' => $loginError]);
    exit;
}

$search = mb_substr(normalize_name(is_string($_GET['q'] ?? null) ? $_GET['q'] : ''), 0, 100);
$status = in_array($_GET['status'] ?? '', ['sijil', 'belum'], true) ? $_GET['status'] : '';

if (($_GET['export'] ?? '') === 'csv') {
    admin_export_csv($search, $status);
    exit;
}

$courseKey = Course::load()->key();
$total = admin_learners_count($courseKey, $search, $status);
$pages = max(1, (int)ceil($total / ADMIN_PAGE_SIZE));
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$page = min($page, $pages);

render('admin', [
    'title'     => 'Panel Admin',
    'bodyClass' => 'admin-page',
    'dash'      => admin_dashboard(),
    'rows'      => admin_learner_summaries(admin_learners_find($courseKey, $search, $status, ADMIN_PAGE_SIZE, ($page - 1) * ADMIN_PAGE_SIZE)),
    'total'     => $total,
    'page'      => $page,
    'pages'     => $pages,
    'search'    => $search,
    'status'    => $status,
]);
