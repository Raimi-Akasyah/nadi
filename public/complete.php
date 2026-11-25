<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';
post_only();
csrf_verify();
$learner = require_learner();
$course = Course::load();
$learnerId = (int)$learner['id'];
$first = $course->firstUnpassedQuiz(learner_passed_quizzes($course, $learnerId));
if ($first !== null) {
    redirect('course.php?' . http_build_query(['lesson' => $first['key']]));
}
$latest = attempt_latest_passed($learnerId, $course->key());
if ($latest === null) {
    // All quizzes count as passed only with a passed attempt or an existing completion.
    redirect('certificate.php');
}
completion_create($learnerId, $course->key(), (int)$latest['id']);
redirect('certificate.php');
