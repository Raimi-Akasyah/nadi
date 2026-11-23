<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$learner = require_learner();
$course = Course::load();
$done = array_flip(progress_keys((int)$learner['id']));

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

$missing = [];
if ($lesson['type'] === 'quiz') {
    foreach ($course->quizRequiredLessons() as $l) {
        if (!isset($done[$l['key']])) {
            $missing[] = $l;
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
    'missing'    => $missing,
]);
