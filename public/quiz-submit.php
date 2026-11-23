<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

post_only();
csrf_verify();
$learner = require_learner();
$course = Course::load();
$learnerId = (int)$learner['id'];

$done = array_flip(progress_keys($learnerId));
foreach ($course->quizRequiredLessons() as $l) {
    if (!isset($done[$l['key']])) {
        abort(403, 'Sila lengkapkan semua modul (Modul 1 hingga Modul 5) sebelum menjawab Kuiz Akhir.');
    }
}

$answers = $_POST['answers'] ?? [];
if (!is_array($answers)) {
    $answers = [];
}

$result = grade_quiz($course->quiz(), $answers);
$attemptId = attempt_create($learnerId, $course->key(), $result);

redirect('course.php?' . http_build_query(['lesson' => $course->quizLesson()['key'], 'attempt' => $attemptId]));
