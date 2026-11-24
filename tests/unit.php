<?php
declare(strict_types=1);

// Unit tests (no database needed). Run: & 'C:\xampp 8.2\php\php.exe' tests/unit.php

require __DIR__ . '/../src/bootstrap.php';

$failures = 0;
function check(bool $cond, string $msg): void
{
    global $failures;
    if ($cond) {
        echo "PASS $msg\n";
    } else {
        $failures++;
        echo "FAIL $msg\n";
    }
}

// --- format_date_ms ---
check(format_date_ms('2026-11-23 10:00:00') === '23 November 2026', "format_date_ms('2026-11-23 10:00:00') === '23 November 2026'");
check(format_date_ms('2026-08-01 09:00:00') === '1 Ogos 2026', "format_date_ms('2026-08-01 09:00:00') === '1 Ogos 2026'");
check(format_date_ms('2026-03-05 00:00:00') === '5 Mac 2026', "format_date_ms uses 'Mac' for March");

// --- Course structure ---
$course = Course::load();
check(count($course->sections()) === 9, 'course has 9 sections');
$quizLessons = array_values(array_filter($course->lessons(), static function ($l) { return $l['type'] === 'quiz'; }));
check(count($quizLessons) === 1, 'exactly 1 quiz lesson');
check(($quizLessons[0]['key'] ?? null) === 'kuiz-akhir', 'quiz lesson key is kuiz-akhir');
check($course->quizLesson()['key'] === 'kuiz-akhir', 'quizLesson() returns kuiz-akhir');
$req = array_map(static function ($l) { return $l['key']; }, $course->quizRequiredLessons());
check($req === ['modul-1-pengenalan', 'modul-2-pengenalan', 'modul-3-pengenalan', 'modul-4-pengenalan', 'modul-5-pengenalan'],
    'quizRequiredLessons() returns modul-1..5-pengenalan');
check(($course->nextLesson('modul-5-pengenalan')['key'] ?? null) === 'kuiz-akhir', "nextLesson('modul-5-pengenalan') is kuiz-akhir");
check($course->prevLesson($course->lessons()[0]['key']) === null, 'prevLesson of first lesson is null');
check(($course->prevLesson('modul-2-pengenalan')['key'] ?? null) === 'modul-1-pengenalan', "prevLesson('modul-2-pengenalan') is modul-1-pengenalan");
check($course->lesson('does-not-exist') === null, 'lesson() returns null for unknown key');
check(count($course->lesson('modul-1-pengenalan')['slides']) === 5, 'modul-1-pengenalan has 5 slides');
check(count($course->sectionLessons('modul-1')) === 1, 'sectionLessons(modul-1) has 1 lesson');
check($course->lessonLabel($course->lesson('modul-1-pengenalan')) === 'PEMBENTANGAN', 'presentation label is PEMBENTANGAN');
check($course->lessonLabel($course->quizLesson()) === 'KUIZ - 10 SOALAN', 'quiz label is KUIZ - 10 SOALAN');
$keys = array_map(static function ($l) { return $l['key']; }, $course->lessons());
check(count($keys) === count(array_unique($keys)), 'lesson keys are unique');

// --- Quiz data integrity ---
$quiz = $course->quiz();
check(($quiz['pass_mark'] ?? null) === 75, 'quiz pass_mark is 75');
check(count($quiz['questions']) === 10, 'quiz has exactly 10 questions');
$ids = array_map(static function ($q) { return $q['id']; }, $quiz['questions']);
check(count($ids) === count(array_unique($ids)), 'quiz question ids are unique');
$okOptions = true;
$okAnswers = true;
foreach ($quiz['questions'] as $q) {
    if (count($q['options']) !== 4 || array_keys($q['options']) !== ['a', 'b', 'c', 'd']) {
        $okOptions = false;
    }
    if (!isset($q['answer']) || !array_key_exists($q['answer'], $q['options'])) {
        $okAnswers = false;
    }
}
check($okOptions, 'every question has 4 options a-d');
check($okAnswers, 'every answer key exists in its options');

// --- validate_registration ---
$valid = [
    'first_name' => 'Ali', 'last_name' => 'Ahmad', 'age' => '30', 'email' => 'ali@example.com',
    'phone' => '+60 12-345 6789', 'address' => 'No 1, Jalan Satu, Kuala Lumpur', 'occupation' => 'Usahawan',
];
[$clean, $errors] = validate_registration($valid);
check($errors === [], 'valid registration has no errors');
check($clean['age'] === 30, 'valid registration casts age to int');

$cases = [
    'missing first_name' => [['first_name' => ''], 'first_name'],
    'age 4'              => [['age' => '4'], 'age'],
    'age abc'            => [['age' => 'abc'], 'age'],
    'age 121'            => [['age' => '121'], 'age'],
    'bad email'          => [['email' => 'not-an-email'], 'email'],
    'phone 12ab'         => [['phone' => '12ab'], 'phone'],
    'empty occupation'   => [['occupation' => '   '], 'occupation'],
    'empty address'      => [['address' => ''], 'address'],
    'missing last_name'  => [['last_name' => ''], 'last_name'],
];
foreach ($cases as $label => [$override, $field]) {
    [, $errs] = validate_registration(array_merge($valid, $override));
    check(isset($errs[$field]) && count($errs) === 1, "validate_registration: $label -> error on $field only");
}
[, $errs] = validate_registration([]);
check(count($errs) === 7, 'validate_registration: empty input -> 7 errors');

// --- grade_quiz ---
$answersWith = static function (array $quiz, int $correct): array {
    $out = [];
    foreach ($quiz['questions'] as $i => $q) {
        if ($i < $correct) {
            $out[$q['id']] = $q['answer'];
        } else {
            $wrong = array_values(array_diff(array_keys($q['options']), [$q['answer']]));
            $out[$q['id']] = $wrong[0];
        }
    }
    return $out;
};
$r = grade_quiz($quiz, $answersWith($quiz, 7));
check($r['correct'] === 7 && $r['total'] === 10 && $r['percent'] === 70.0 && $r['passed'] === false, 'grade_quiz: 7/10 -> 70% and fails');
$r = grade_quiz($quiz, $answersWith($quiz, 8));
check($r['correct'] === 8 && $r['percent'] === 80.0 && $r['passed'] === true, 'grade_quiz: 8/10 -> 80% and passes');
$r = grade_quiz($quiz, $answersWith($quiz, 10));
check($r['percent'] === 100.0 && $r['passed'] === true, 'grade_quiz: 10/10 -> 100%');
$small = ['pass_mark' => 75, 'questions' => array_slice($quiz['questions'], 0, 4)];
$r = grade_quiz($small, $answersWith($small, 3));
check($r['total'] === 4 && $r['percent'] === 75.0 && $r['passed'] === true, 'grade_quiz: 3/4 -> 75% passes (boundary)');
$r = grade_quiz($quiz, []);
check($r['correct'] === 0 && $r['percent'] === 0.0 && $r['passed'] === false && $r['answers'] === [], 'grade_quiz: empty answers -> 0');
$q1 = $quiz['questions'][0];
$r = grade_quiz($quiz, ['zz' => 'a', $q1['id'] => 'x', $quiz['questions'][1]['id'] => ['a']]);
check($r['correct'] === 0 && $r['answers'] === [], 'grade_quiz: unknown qid, unknown option and non-string ignored');
$r = grade_quiz($quiz, [$q1['id'] => $q1['answer'], 'zz' => 'a']);
check($r['correct'] === 1 && $r['answers'] === [$q1['id'] => $q1['answer']], 'grade_quiz: sanitized answers keep only known qids');

// --- certificate PDF ---
$pdf = render_certificate_pdf([
    'first_name' => 'Ali', 'last_name' => 'Ahmad', 'course_title' => $course->title(),
    'date_text' => format_date_ms('2026-11-23 10:00:00'), 'code' => 'NADI-0123456789', 'issuer' => 'NADI',
]);
check(strncmp($pdf, '%PDF', 4) === 0, 'certificate PDF starts with %PDF');
check(strpos($pdf, 'Ali Ahmad') !== false, "certificate PDF contains 'Ali Ahmad'");
check(strpos($pdf, '23 November 2026') !== false, "certificate PDF contains '23 November 2026'");
check(strpos($pdf, 'NADI-0123456789') !== false, 'certificate PDF contains the code');
check(pdf_text('Caf' . "\u{00E9}") === "Caf\xE9", 'pdf_text converts UTF-8 to windows-1252');

// --- Learner idle timeout ---
$now = 1_800_000_000;
check(LEARNER_IDLE_MINUTES === 30, 'learner idle timeout is 30 minutes');
check(!learner_session_expired($now - 60, $now), 'session active 1 minute ago is not expired');
check(!learner_session_expired($now - 30 * 60, $now), 'session active exactly 30 minutes ago is not expired');
check(learner_session_expired($now - 30 * 60 - 1, $now), 'session idle for more than 30 minutes is expired');
check(learner_session_expired(0, $now), 'session with no recorded activity is expired');

echo $failures === 0 ? "\nAll tests passed.\n" : "\n$failures test(s) failed.\n";
exit($failures === 0 ? 0 : 1);
