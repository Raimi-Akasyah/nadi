<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$course = Course::load();
$learner = current_learner();
render('landing', [
    'title'     => $course->title(),
    'course'    => $course,
    'tracks'    => Course::all(),
    'learner'   => $learner,
    'signedOut' => $learner === null && isset($_GET['keluar']),
    'scripts'   => ['assets/js/tabs.js'],
]);
