<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$learner = require_learner();
$learnerId = (int)$learner['id'];

// The track (E-Learning or Self-Learning): the one that owns ?lesson=, else ?track=, else the one last opened.
$lessonKey = $_GET['lesson'] ?? null;
$hasLessonKey = $lessonKey !== null && $lessonKey !== '';
$course = $hasLessonKey && is_string($lessonKey) ? Course::forLesson($lessonKey) : null;
if ($hasLessonKey && $course === null) {
    abort(404, 'Pelajaran tidak dijumpai.');
}
if ($course === null) {
    $track = $_GET['track'] ?? null;
    $course = Course::load(Course::isTrack($track) ? $track : current_track());
}
remember_track($course->track());

$passed = learner_passed_quizzes($course, $learnerId);
// A passed quiz counts as a completed lesson (also for learners who passed the old Kuiz Akhir).
$done = array_flip(progress_keys($learnerId)) + $passed;

if (!$hasLessonKey) {
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
    $lesson = $course->lesson($lessonKey);
}

$slideCount = $lesson['type'] === 'presentation' ? count($lesson['slides']) : 0;
$slide = 1;
if ($slideCount > 0) {
    $requested = filter_var($_GET['slide'] ?? 1, FILTER_VALIDATE_INT);
    $slide = max(1, min($slideCount, $requested === false ? 1 : (int)$requested));
}

$percent = $course->percentDone($done);

$lock = $course->lockReason($lesson, $done, $passed);

$quiz = null;
$attempt = null;
$attemptNo = 0;
$completion = null;
if ($lock !== null) {
    http_response_code(403);
} elseif ($lesson['type'] === 'video') {
    // Starts the server-side clock for "watched to the end" (see lesson-complete.php).
    video_mark_opened($learnerId, $lesson['key']);
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
    'tracks'     => Course::all(),
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
