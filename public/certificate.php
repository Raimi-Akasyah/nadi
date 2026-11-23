<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$learner = require_learner();
$course = Course::load();
$completion = completion_find((int)$learner['id'], $course->key());
if ($completion === null) {
    redirect('course.php?' . http_build_query(['lesson' => $course->quizLesson()['key']]));
}

render('certificate', [
    'title'      => 'Sijil',
    'learner'    => $learner,
    'course'     => $course,
    'completion' => $completion,
]);
