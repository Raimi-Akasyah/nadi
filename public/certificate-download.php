<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$learner = require_learner();
$course = Course::load();
$completion = completion_find((int)$learner['id'], $course->key());
if ($completion === null) {
    $quizzes = $course->quizLessons();
    $target = $course->firstUnpassedQuiz(learner_passed_quizzes($course, (int)$learner['id'])) ?? $quizzes[count($quizzes) - 1];
    redirect('course.php?' . http_build_query(['lesson' => $target['key']]));
}

$pdf = render_certificate_pdf([
    'first_name'   => $learner['first_name'],
    'last_name'    => $learner['last_name'],
    'course_title' => $course->title(),
    'date_text'    => format_date_ms($completion['completed_at']),
    'code'         => $completion['certificate_code'],
    'issuer'       => config()['certificate']['issuer'] ?? 'NADI',
]);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Sijil-' . $completion['certificate_code'] . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
echo $pdf;
