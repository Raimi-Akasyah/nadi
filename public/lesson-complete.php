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
$passed = learner_passed_quizzes($course, (int)$learner['id']);
$lock = $course->lockReason($lesson, array_flip(progress_keys((int)$learner['id'])) + $passed, $passed);
if ($lock !== null) {
    abort(403, $lock['message']);
}

progress_mark((int)$learner['id'], $lesson['key']);

$next = $course->nextLesson($lesson['key']);
$target = $next !== null ? $next['key'] : $lesson['key'];
redirect('course.php?' . http_build_query(['lesson' => $target]));
