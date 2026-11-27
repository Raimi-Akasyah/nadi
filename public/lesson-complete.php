<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

post_only();
csrf_verify();
$learner = require_learner();
$learnerId = (int)$learner['id'];

$key = $_POST['lesson'] ?? '';
$course = is_string($key) ? Course::forLesson($key) : null;
$lesson = $course !== null ? $course->lesson($key) : null;
if ($lesson === null || !in_array($lesson['type'], ['presentation', 'video'], true)) {
    abort(400, 'Pelajaran tidak sah.');
}
$passed = learner_passed_quizzes($course, $learnerId);
$done = array_flip(progress_keys($learnerId)) + $passed;
$lock = $course->lockReason($lesson, $done, $passed);
if ($lock !== null) {
    abort(403, $lock['message']);
}
// A video must have been watched to the end. The player in course.js enforces that; here the server checks that
// enough time has passed since the lesson was opened. Rewatching a completed video needs no check.
if ($lesson['type'] === 'video' && !isset($done[$lesson['key']])
    && !video_watched_long_enough(video_opened_at($learnerId, $lesson['key']), (int)$lesson['duration'], time())) {
    abort(403, 'Sila tonton video sampai habis sebelum meneruskan.');
}

progress_mark($learnerId, $lesson['key']);

$next = $course->nextLesson($lesson['key']);
$target = $next !== null ? $next['key'] : $lesson['key'];
redirect('course.php?' . http_build_query(['lesson' => $target]));
