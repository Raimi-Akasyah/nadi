<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$course = Course::load();
render('landing', [
    'title'   => $course->title(),
    'course'  => $course,
    'learner' => current_learner(),
]);
