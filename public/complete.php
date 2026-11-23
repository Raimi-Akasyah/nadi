<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

post_only();
csrf_verify();
$learner = require_learner();
$course = Course::load();
$learnerId = (int)$learner['id'];
$quizKey = $course->quizLesson()['key'];

$passed = attempt_latest_passed($learnerId, $course->key());
if ($passed === null) {
    redirect('course.php?' . http_build_query(['lesson' => $quizKey]));
}

completion_create($learnerId, $course->key(), (int)$passed['id']);
progress_mark($learnerId, $quizKey);

redirect('certificate.php');
