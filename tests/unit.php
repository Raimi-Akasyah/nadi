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

echo $failures === 0 ? "\nAll tests passed.\n" : "\n$failures test(s) failed.\n";
exit($failures === 0 ? 0 : 1);
