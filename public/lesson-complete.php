<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

post_only();
csrf_verify();
$learner = require_learner();
$course = Course::load();

$key = $_POST['lesson'] ?? '';
$lesson = is_string($key) ? $course->lesson($key) : null;
if ($lesson === null || $lesson['type'] !== 'presentation') {
    abort(400, 'Pelajaran tidak sah.');
}

progress_mark((int)$learner['id'], $lesson['key']);

$next = $course->nextLesson($lesson['key']);
$target = $next !== null ? $next['key'] : $lesson['key'];
redirect('course.php?' . http_build_query(['lesson' => $target]));
