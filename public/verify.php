<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$course = Course::load();
$raw = $_GET['code'] ?? '';
$code = is_string($raw) ? strtoupper(trim($raw)) : '';
$searched = $code !== '';
$found = null;
if ($searched && preg_match('/^NADI-[0-9A-F]{10}$/', $code) === 1) {
    $found = completion_find_by_code($code);
}

render('verify', [
    'title'    => 'Pengesahan Sijil',
    'course'   => $course,
    'code'     => $code,
    'searched' => $searched,
    'found'    => $found,
]);
