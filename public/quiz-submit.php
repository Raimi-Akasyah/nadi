<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';
post_only();
csrf_verify();
$learner = require_learner();
$learnerId = (int)$learner['id'];
$key = $_POST['quiz'] ?? '';
$course = is_string($key) ? Course::forLesson($key) : null;
$lesson = $course !== null ? $course->lesson($key) : null;
if ($lesson === null || $lesson['type'] !== 'quiz') {
    abort(400, 'Kuiz tidak sah.');
}
$passed = learner_passed_quizzes($course, $learnerId);
$done = array_flip(progress_keys($learnerId)) + $passed;
$lock = $course->lockReason($lesson, $done, $passed);
if ($lock !== null) {
    abort(403, $lock['message']);
}
$answers = $_POST['answers'] ?? [];
if (!is_array($answers)) {
    $answers = [];
}
$result = grade_quiz($course->quiz($lesson['key']), $answers);
$attemptId = attempt_create($learnerId, $course->key(), $lesson['key'], $result);
if ($result['passed']) {
    progress_mark($learnerId, $lesson['key']);
}
redirect('course.php?' . http_build_query(['lesson' => $lesson['key'], 'attempt' => $attemptId]));
