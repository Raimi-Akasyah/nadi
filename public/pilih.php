<?php
declare(strict_types=1);

// "Pilih Cara Belajar": after registering (or Sambung Semula) the learner chooses E-Learning (slides) or
// Self-Learning (video). Both tracks share one completion, so finishing either one gives the certificate.
require __DIR__ . '/../src/bootstrap.php';

$learner = require_learner();
$learnerId = (int)$learner['id'];
$progress = array_flip(progress_keys($learnerId));
$cards = [];
foreach (Course::all() as $course) {
    $cards[] = [
        'course'  => $course,
        'percent' => $course->percentDone($progress + learner_passed_quizzes($course, $learnerId)),
    ];
}

render('pilih', [
    'title'      => 'Pilih Cara Belajar',
    'learner'    => $learner,
    'cards'      => $cards,
    'completion' => completion_find($learnerId, Course::load()->key()),
    'flash'      => flash_take(),
]);
