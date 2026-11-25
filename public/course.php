<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$learner = require_learner();
$course = Course::load();
$learnerId = (int)$learner['id'];
$passed = learner_passed_quizzes($course, $learnerId);
// A passed quiz counts as a completed lesson (also for learners who passed the old Kuiz Akhir).
$done = array_flip(progress_keys($learnerId)) + $passed;

$lessonKey = $_GET['lesson'] ?? null;
if ($lessonKey === null || $lessonKey === '') {
    $lesson = null;
    foreach ($course->lessons() as $l) {
        if (!isset($done[$l['key']])) {
            $lesson = $l;
            break;
        }
    }
    if ($lesson === null) {
        $lesson = $course->lessons()[0];
    }
} else {
    $lesson = is_string($lessonKey) ? $course->lesson($lessonKey) : null;
    if ($lesson === null) {
        abort(404, 'Pelajaran tidak dijumpai.');
    }
}

$slideCount = $lesson['type'] === 'presentation' ? count($lesson['slides']) : 0;
$slide = 1;
if ($slideCount > 0) {
    $requested = filter_var($_GET['slide'] ?? 1, FILTER_VALIDATE_INT);
    $slide = max(1, min($slideCount, $requested === false ? 1 : (int)$requested));
}

$allLessons = $course->lessons();
$completedCount = 0;
foreach ($allLessons as $l) {
    if (isset($done[$l['key']])) {
        $completedCount++;
    }
}
$percent = count($allLessons) > 0 ? (int)round($completedCount * 100 / count($allLessons)) : 0;

$lock = $course->lockReason($lesson, $done, $passed);

$quiz = null;
$attempt = null;
$attemptNo = 0;
$completion = null;
if ($lock !== null) {
    http_response_code(403);
} elseif ($lesson['type'] === 'quiz') {
    $quiz = $course->quiz($lesson['key']);
    $completion = completion_find($learnerId, $course->key());
    $attemptNo = attempt_count($learnerId, $course->key(), $lesson['key']) + 1;
    $attemptId = filter_var($_GET['attempt'] ?? null, FILTER_VALIDATE_INT);
    if ($attemptId !== false && $attemptId !== null) {
        $attempt = attempt_find((int)$attemptId, $learnerId);
        if ($attempt === null || $attempt['quiz_key'] !== $lesson['key']) {
            abort(404, 'Percubaan kuiz tidak dijumpai.');
        }
    }
}

render('course', [
    'title'      => $lesson['title'],
    'bodyClass'  => 'course-page',
    'hideHeader' => true,
    'scripts'    => ['assets/js/course.js'],
    'course'     => $course,
    'learner'    => $learner,
    'lesson'     => $lesson,
    'done'       => $done,
    'slide'      => $slide,
    'slideCount' => $slideCount,
    'percent'    => $percent,
    'lock'       => $lock,
    'passed'     => $passed,
    'allPassed'  => $course->allQuizzesPassed($passed),
    'nextLesson' => $course->nextLesson($lesson['key']),
    'quiz'       => $quiz,
    'attempt'    => $attempt,
    'attemptNo'  => $attemptNo,
    'completion' => $completion,
    'flash'      => flash_take(),
]);
