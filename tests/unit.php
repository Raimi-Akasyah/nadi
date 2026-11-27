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
check(count($course->sections()) === 7, 'course has 7 sections');
check(count($course->lessons()) === 12, 'course has 12 lessons');
$quizKeys = array_map(static function ($l) { return $l['key']; }, $course->quizLessons());
check($quizKeys === ['kuiz-modul-1', 'kuiz-modul-2', 'kuiz-modul-3', 'kuiz-modul-4', 'kuiz-modul-5'], 'quizLessons() returns kuiz-modul-1..5');
$lastOk = true;
foreach ($course->quizLessons() as $n => $ql) {
    $sec = $course->sectionLessons($ql['section_key']);
    if ($ql['section_key'] !== 'modul-' . ($n + 1) || $sec[count($sec) - 1]['key'] !== $ql['key'] || $ql['title'] !== 'Kuiz Modul ' . ($n + 1)) {
        $lastOk = false;
    }
}
check($lastOk, 'each Kuiz Modul N is the last lesson of section modul-N');
check($course->lesson('kuiz-akhir') === null, 'kuiz-akhir lesson is removed');
check(($course->nextLesson('modul-1-pengenalan')['key'] ?? null) === 'kuiz-modul-1', "nextLesson('modul-1-pengenalan') is kuiz-modul-1");
check(($course->nextLesson('kuiz-modul-1')['key'] ?? null) === 'modul-2-pengenalan', "nextLesson('kuiz-modul-1') is modul-2-pengenalan");
check(($course->nextLesson('kuiz-modul-5')['key'] ?? null) === 'penutup', "nextLesson('kuiz-modul-5') is penutup");
check($course->prevLesson($course->lessons()[0]['key']) === null, 'prevLesson of first lesson is null');
check(($course->prevLesson('modul-2-pengenalan')['key'] ?? null) === 'kuiz-modul-1', "prevLesson('modul-2-pengenalan') is kuiz-modul-1");
check($course->lesson('does-not-exist') === null, 'lesson() returns null for unknown key');
check(count($course->lesson('modul-1-pengenalan')['slides']) === 3, 'modul-1-pengenalan has 3 slides');
$allLessons = $course->lessons();
check($allLessons[0]['key'] === 'pengenalan', "first lesson is 'pengenalan'");
check($allLessons[count($allLessons) - 1]['key'] === 'penutup', "last lesson is 'penutup'");
check(($course->prevLesson('modul-1-pengenalan')['key'] ?? null) === 'pengenalan', "prevLesson('modul-1-pengenalan') is pengenalan");
foreach (['pre-survey', 'post-survey', 'bonus-konten'] as $gone) {
    check($course->lesson($gone) === null, "lesson '$gone' is removed");
}
$slidesOk = true;
foreach ($allLessons as $l) {
    if ($l['type'] !== 'presentation') {
        continue;
    }
    foreach ($l['slides'] as $s) {
        $img = (string)($s['image'] ?? '');
        if ((string)($s['title'] ?? '') === '' || $img === '') {
            $slidesOk = false;
            continue;
        }
        foreach ([APP_ROOT . '/public/' . $img, APP_ROOT . '/docs/' . $img] as $path) {
            if (!is_file($path) || filesize($path) >= 400000) {
                $slidesOk = false;
                echo "  bad slide image: $path\n";
            }
        }
    }
}
check($slidesOk, 'every slide has a title and an image under 400000 bytes in public/ and docs/');
check(count($course->sectionLessons('modul-1')) === 2, 'sectionLessons(modul-1) has 2 lessons');
check($course->lessonLabel($course->lesson('modul-1-pengenalan')) === 'PEMBENTANGAN', 'presentation label is PEMBENTANGAN');
check($course->lessonLabel($course->lesson('kuiz-modul-1')) === 'KUIZ - 3 SOALAN', 'kuiz-modul-1 label is KUIZ - 3 SOALAN');
check($course->lessonLabel($course->lesson('kuiz-modul-4')) === 'KUIZ - 1 SOALAN', 'kuiz-modul-4 label is KUIZ - 1 SOALAN');
$keys = array_map(static function ($l) { return $l['key']; }, $course->lessons());
check(count($keys) === count(array_unique($keys)), 'lesson keys are unique');

// --- Locking (sequential modules) ---
$L = static function (string $k) use ($course): array { return $course->lesson($k); };
$none = [];
check($course->lockReason($L('pengenalan'), $none, $none) === null, 'pengenalan is always open');
check($course->lockReason($L('modul-1-pengenalan'), $none, $none) === null, 'modul-1-pengenalan is open from the start');
$lr = $course->lockReason($L('kuiz-modul-1'), $none, $none);
check($lr !== null && strpos($lr['message'], 'Sila lengkapkan semua pelajaran dalam Modul 1') === 0
    && array_column($lr['links'], 'key') === ['modul-1-pengenalan'], 'kuiz-modul-1 locked until modul-1-pengenalan is done (links it)');
check($course->lockReason($L('kuiz-modul-1'), ['modul-1-pengenalan' => true], $none) === null, 'kuiz-modul-1 opens after modul-1-pengenalan');
$lr = $course->lockReason($L('modul-2-pengenalan'), ['modul-1-pengenalan' => true], $none);
check($lr !== null && $lr['message'] === 'Modul ini dikunci. Sila lulus Kuiz Modul 1 terlebih dahulu.'
    && array_column($lr['links'], 'key') === ['kuiz-modul-1'], 'modul-2-pengenalan locked with no passes (links Kuiz Modul 1)');
check($course->lockReason($L('kuiz-modul-2'), ['modul-2-pengenalan' => true], $none) !== null, 'kuiz-modul-2 locked while Kuiz Modul 1 unpassed');
$p1 = $course->effectivePassed(['kuiz-modul-1'], false);
check($p1 === ['kuiz-modul-1' => true], "effectivePassed(['kuiz-modul-1']) is that quiz only");
check($course->lockReason($L('modul-2-pengenalan'), $none, $p1) === null, 'modul-2-pengenalan opens after Kuiz Modul 1 is passed');
check($course->lockReason($L('modul-3-pengenalan'), $none, $p1) !== null, 'modul-3-pengenalan stays locked after only Kuiz Modul 1');
$p4 = $course->effectivePassed(['kuiz-modul-1', 'kuiz-modul-2', 'kuiz-modul-3', 'kuiz-modul-4'], false);
$lr = $course->lockReason($L('penutup'), $none, $p4);
check($lr !== null && $lr['message'] === 'Penutup dibuka selepas anda lulus kesemua kuiz modul (Modul 1 hingga Modul 5).'
    && array_column($lr['links'], 'key') === ['kuiz-modul-5'], 'penutup locked until all 5 passed (links first unpassed)');
check(!$course->allQuizzesPassed($p4) && ($course->firstUnpassedQuiz($p4)['key'] ?? null) === 'kuiz-modul-5', 'firstUnpassedQuiz after 4 passes is kuiz-modul-5');
$p5 = $course->effectivePassed(['kuiz-modul-5', 'kuiz-modul-1', 'kuiz-modul-2', 'kuiz-modul-3', 'kuiz-modul-4', 'other'], false);
check($course->allQuizzesPassed($p5) && $course->firstUnpassedQuiz($p5) === null, 'all 5 passed -> allQuizzesPassed');
check($course->lockReason($L('penutup'), $none, $p5) === null, 'penutup opens after all 5 passed');
check(count($p5) === 5 && !isset($p5['other']), 'effectivePassed ignores unknown quiz keys');
$all5 = array_fill_keys($quizKeys, true);
check($course->effectivePassed([Course::LEGACY_QUIZ_KEY], false) === $all5, "effectivePassed with legacy 'kuiz-akhir' -> all 5 passed");
check($course->effectivePassed([], true) === $all5, 'effectivePassed with a completion -> all 5 passed');
check($course->effectivePassed([], false) === [], 'effectivePassed with nothing -> none');
check($course->lockReason($L('modul-5-pengenalan'), $none, $all5) === null, 'legacy learner: modul-5-pengenalan open');
check($course->passMark() === 75, 'passMark() is 75');

// --- Quiz data integrity ---
$allQuestions = [];
$counts = [];
foreach ($quizKeys as $qk) {
    $qz = $course->quiz($qk);
    check(($qz['pass_mark'] ?? null) === 75, "$qk pass_mark is 75");
    check(($qz['title'] ?? null) === $course->lesson($qk)['title'], "$qk quiz title matches the lesson title");
    $counts[] = count($qz['questions']);
    $allQuestions = array_merge($allQuestions, $qz['questions']);
}
check($counts === [3, 2, 2, 1, 2], 'module quizzes have 3,2,2,1,2 questions');
check(count($allQuestions) === 10, 'quizzes have 10 questions in total');
$ids = array_map(static function ($q) { return $q['id']; }, $allQuestions);
check($ids === ['q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7', 'q8', 'q9', 'q10'], 'question ids q1..q10 in order and unique across quizzes');
$okOptions = true;
$okAnswers = true;
foreach ($allQuestions as $q) {
    if (count($q['options']) !== 4 || array_keys($q['options']) !== ['a', 'b', 'c', 'd']) {
        $okOptions = false;
    }
    if (!isset($q['answer']) || !array_key_exists($q['answer'], $q['options'])) {
        $okAnswers = false;
    }
}
check($okOptions, 'every question has 4 options a-d');
check($okAnswers, 'every answer key exists in its options');
check(array_column($allQuestions, 'answer') === ['b', 'c', 'c', 'a', 'b', 'a', 'b', 'b', 'c', 'c'], 'quiz answer key is b,c,c,a,b,a,b,b,c,c');
$threw = false;
try {
    $course->quiz('modul-1-pengenalan');
} catch (RuntimeException $ex) {
    $threw = true;
}
check($threw, 'quiz() throws for a non-quiz lesson');
// --- validate_registration ---
$valid = [
    'full_name' => 'Ali bin Ahmad', 'age' => '30', 'email' => 'ali@example.com',
    'phone' => '+60 12-345 6789', 'address' => 'No 1, Jalan Satu, Kuala Lumpur', 'occupation' => 'Usahawan',
];
[$clean, $errors] = validate_registration($valid);
check($errors === [], 'valid registration has no errors');
check($clean['age'] === 30, 'valid registration casts age to int');

$cases = [
    'missing full_name'  => [['full_name' => ''], 'full_name'],
    'spaces full_name'   => [['full_name' => "  \t "], 'full_name'],
    'digits full_name'   => [['full_name' => '12345'], 'full_name'],
    'long full_name'     => [['full_name' => str_repeat('a', FULL_NAME_MAX + 1)], 'full_name'],
    'age 4'              => [['age' => '4'], 'age'],
    'age abc'            => [['age' => 'abc'], 'age'],
    'age 121'            => [['age' => '121'], 'age'],
    'bad email'          => [['email' => 'not-an-email'], 'email'],
    'phone 12ab'         => [['phone' => '12ab'], 'phone'],
    'empty occupation'   => [['occupation' => '   '], 'occupation'],
    'empty address'      => [['address' => ''], 'address'],
];
foreach ($cases as $label => [$override, $field]) {
    [, $errs] = validate_registration(array_merge($valid, $override));
    check(isset($errs[$field]) && count($errs) === 1, "validate_registration: $label -> error on $field only");
}
[, $errs] = validate_registration([]);
check(count($errs) === 6, 'validate_registration: empty input -> 6 errors');
[$clean] = validate_registration(array_merge($valid, ['full_name' => "  Siti \t Nur   Aminah "]));
check($clean['full_name'] === 'Siti Nur Aminah' && !isset($clean['first_name']), 'validate_registration collapses spaces in the full name');
check(learner_name(['full_name' => 'Siti Aminah', 'first_name' => 'X', 'last_name' => 'Y']) === 'Siti Aminah', 'learner_name() uses full_name');
check(learner_name(['full_name' => '', 'first_name' => ' Ali ', 'last_name' => 'Ahmad']) === 'Ali Ahmad', 'learner_name() falls back to first + last name');
check(name_initials('siti nur aminah') === 'SN' && name_initials('Ali') === 'A' && name_initials('') === '', 'name_initials()');

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
$quiz = $course->quiz('kuiz-modul-1');
$r = grade_quiz($quiz, $answersWith($quiz, 2));
check($r['correct'] === 2 && $r['total'] === 3 && $r['percent'] === 66.67 && $r['passed'] === false, 'grade_quiz: Kuiz Modul 1 2/3 -> 66.67% and fails');
$r = grade_quiz($quiz, $answersWith($quiz, 3));
check($r['correct'] === 3 && $r['percent'] === 100.0 && $r['passed'] === true, 'grade_quiz: Kuiz Modul 1 3/3 -> 100% and passes');
$quiz2 = $course->quiz('kuiz-modul-2');
$r = grade_quiz($quiz2, $answersWith($quiz2, 1));
check($r['correct'] === 1 && $r['total'] === 2 && $r['percent'] === 50.0 && $r['passed'] === false, 'grade_quiz: Kuiz Modul 2 1/2 -> 50% and fails');
$small = ['pass_mark' => 75, 'questions' => array_slice($allQuestions, 0, 4)];
$r = grade_quiz($small, $answersWith($small, 3));
check($r['total'] === 4 && $r['percent'] === 75.0 && $r['passed'] === true, 'grade_quiz: 3/4 -> 75% passes (boundary)');
$r = grade_quiz($quiz, []);
check($r['correct'] === 0 && $r['percent'] === 0.0 && $r['passed'] === false && $r['answers'] === [], 'grade_quiz: empty answers -> 0');
$q1 = $quiz['questions'][0];
$r = grade_quiz($quiz, ['zz' => 'a', $q1['id'] => 'x', $quiz['questions'][1]['id'] => ['a']]);
check($r['correct'] === 0 && $r['answers'] === [], 'grade_quiz: unknown qid, unknown option and non-string ignored');
$r = grade_quiz($quiz, [$q1['id'] => $q1['answer'], 'zz' => 'a', 'q4' => 'a']);
check($r['correct'] === 1 && $r['answers'] === [$q1['id'] => $q1['answer']], 'grade_quiz: sanitized answers keep only this quiz\'s qids');
// --- certificate PDF ---
$pdf = render_certificate_pdf([
    'name' => 'Ali Ahmad', 'course_title' => $course->title(),
    'date_text' => format_date_ms('2026-11-23 10:00:00'), 'code' => 'NADI-0123456789', 'issuer' => 'NADI',
]);
check(strncmp($pdf, '%PDF', 4) === 0, 'certificate PDF starts with %PDF');
check(strpos($pdf, 'Ali Ahmad') !== false, "certificate PDF contains 'Ali Ahmad'");
check(strpos($pdf, '23 November 2026') !== false, "certificate PDF contains '23 November 2026'");
check(strpos($pdf, 'NADI-0123456789') !== false, 'certificate PDF contains the code');
check(substr_count($pdf, '/Subtype /Image') >= 1, 'certificate PDF embeds the template image');
$longLast = str_repeat('Abcdefghij', 6);
$pdf = render_certificate_pdf([
    'name' => 'Ali ' . $longLast, 'course_title' => $course->title(),
    'date_text' => format_date_ms('2026-11-23 10:00:00'), 'code' => 'NADI-0123456789', 'issuer' => 'NADI',
]);
check(strpos($pdf, 'Ali ' . $longLast) !== false, 'certificate PDF contains a 60-character last name in full');
check(pdf_text('Caf' . "\u{00E9}") === "Caf\xE9", 'pdf_text converts UTF-8 to windows-1252');
$certCfg = require APP_ROOT . '/content/certificate.php';
$fpdf = new FPDF('L', 'mm', 'A4');
$measure = static function (string $text, float $size) use ($fpdf): float {
    $fpdf->SetFont('Helvetica', 'B', $size);
    return $fpdf->GetStringWidth(pdf_text($text));
};
$layout = certificate_name_layout($measure, 'Ali Ahmad', $certCfg['name']);
check($layout['lines'] === ['Ali Ahmad'] && $layout['size'] === 26.0, 'certificate_name_layout: Ali Ahmad -> one line at 26 pt');
$huge = str_repeat('a', 100) . ' ' . str_repeat('b', 100);
$layout = certificate_name_layout($measure, $huge, $certCfg['name']);
$fits = true;
foreach ($layout['lines'] as $line) {
    $fits = $fits && $measure($line, $layout['size']) <= $certCfg['name']['max_width'];
}
check(count($layout['lines']) <= 2 && $fits, 'certificate_name_layout: 201-character name fits on at most 2 lines of 250 mm');
check(implode(' ', $layout['lines']) === $huge, 'certificate_name_layout: every character of the long name is kept');
$tplPublic = APP_ROOT . '/public/' . $certCfg['template'];
$tplDocs = APP_ROOT . '/docs/' . $certCfg['template'];
check(is_file($tplPublic) && is_file($tplDocs) && sha1_file($tplPublic) === sha1_file($tplDocs), 'certificate template.png in public/ and docs/ is identical');
[$tw, $th] = getimagesize($tplPublic);
check(abs(($tw / $th) / (297 / 210) - 1) < 0.01, 'certificate template has the A4 landscape aspect ratio (within 1%)');

// --- Sambung Semula: e-mail validation and rate limit ---
check(validate_resume_login(['login' => '  a@example.com ']) === ['email', 'a@example.com', null], 'resume e-mail is trimmed and accepted');
check(validate_resume_login(['login' => '  siti   AMINAH ']) === ['name', 'siti AMINAH', null], 'resume full name: spaces collapsed, accepted');
check(validate_resume_login([])[2] !== null, 'empty resume login is rejected');
check(validate_resume_login(['login' => 'bukan@emel'])[2] === 'Alamat e-mel tidak sah.', 'invalid resume e-mail is rejected');
check(validate_resume_login(['login' => '123'])[2] === 'Nama penuh tidak sah.', 'resume name without letters is rejected');
check(validate_resume_login(['login' => ['x']])[2] !== null, 'array resume login is rejected');
check(!resume_rate_limited(['ip' => RESUME_MAX_PER_IP - 1, 'email' => RESUME_MAX_PER_EMAIL - 1]), 'resume below both limits is allowed');
check(resume_rate_limited(['ip' => RESUME_MAX_PER_IP, 'email' => 0]), 'resume at the per-IP limit is refused');
check(resume_rate_limited(['ip' => 0, 'email' => RESUME_MAX_PER_EMAIL]), 'resume at the per-e-mail limit is refused');
check(RESUME_MAX_PER_IP >= 30, 'per-IP resume limit leaves room for 30 learners behind one IP');

// --- Learner idle timeout ---
$now = 1_800_000_000;
check(LEARNER_IDLE_MINUTES === 30, 'learner idle timeout is 30 minutes');
check(!learner_session_expired($now - 60, $now), 'session active 1 minute ago is not expired');
check(!learner_session_expired($now - 30 * 60, $now), 'session active exactly 30 minutes ago is not expired');
check(learner_session_expired($now - 30 * 60 - 1, $now), 'session idle for more than 30 minutes is expired');
check(learner_session_expired(0, $now), 'session with no recorded activity is expired');

// --- Tracks: E-Learning and Self-Learning ---
$self = Course::load('self-learning');
check(array_keys(Course::TRACKS) === ['e-learning', 'self-learning'] && Course::DEFAULT_TRACK === 'e-learning', 'tracks are e-learning (default) and self-learning');
check(Course::load() === Course::load('e-learning') && $course->track() === 'e-learning' && $self->track() === 'self-learning', 'load() is the e-learning track');
check($self->key() === $course->key() && $self->title() === $course->title(), 'both tracks share the course key and title (one completion, one certificate)');
check($course->trackTitle() === 'E-Learning' && $self->trackTitle() === 'Self-Learning', 'track titles');
check(Course::isTrack('self-learning') && !Course::isTrack('x') && !Course::isTrack(['e-learning']) && !Course::isTrack(null), 'isTrack()');
$threw = false;
try {
    Course::load('x');
} catch (InvalidArgumentException $ex) {
    $threw = true;
}
check($threw, 'load() throws for an unknown track');
$selfKeys = array_column($self->lessons(), 'key');
check($selfKeys === ['self-pengenalan', 'self-video', 'kuiz-self-learning', 'self-penutup'], 'self-learning: Pengenalan video, main video, Kuiz Self-Learning, Penutup video');
check(array_column($self->sections(), 'key') === ['pengenalan', 'modul-1', 'penutup'], 'self-learning sections: Pengenalan, Modul 1, Penutup');
$videosOk = true;
foreach ($self->lessons() as $l) {
    if ($l['type'] !== 'quiz' && ($l['type'] !== 'video' || preg_match('/^[A-Za-z0-9_-]{11}$/', (string)$l['youtube']) !== 1
        || !is_int($l['duration']) || $l['duration'] <= 0)) {
        $videosOk = false;
    }
}
check($videosOk, 'every self-learning lesson except the quiz is a video with a YouTube id and a duration');
check($self->lockReason($self->lesson('self-pengenalan'), $none, $none) === null, 'self-learning Pengenalan is always open');
$lr = $self->lockReason($self->lesson('self-penutup'), ['self-video' => true], $none);
check($lr !== null && $lr['message'] === 'Penutup dibuka selepas anda lulus Kuiz Self-Learning.'
    && array_column($lr['links'], 'key') === ['kuiz-self-learning'], 'self-learning Penutup locked until Kuiz Self-Learning (links it)');
check($self->lockReason($self->lesson('self-penutup'), $none, ['kuiz-self-learning' => true]) === null, 'self-learning Penutup opens after the quiz');
check(array_intersect($selfKeys, $keys) === [], 'lesson keys are unique across tracks');
check(Course::forLesson('self-video') === $self && Course::forLesson('modul-1-pengenalan') === $course && Course::forLesson('nope') === null, 'forLesson() finds the owning track');
$video = $self->lesson('self-video');
check(preg_match('/^[A-Za-z0-9_-]{11}$/', (string)$video['youtube']) === 1 && is_int($video['duration']) && $video['duration'] > 0, 'video lesson has an 11-character YouTube id and a duration in seconds');
check($self->lessonLabel($video) === 'VIDEO - ' . format_duration($video['duration']), 'video label is VIDEO - m:ss');
check(Course::lessonIcon($video) === 'video' && Course::lessonIcon($L('kuiz-modul-1')) === 'quiz' && Course::lessonIcon($L('pengenalan')) === 'slides', 'lessonIcon() per type');
$selfQuiz = $self->quiz('kuiz-self-learning');
check(array_column($selfQuiz['questions'], 'id') === $ids && $selfQuiz['pass_mark'] === 75, 'Kuiz Self-Learning asks the 10 module questions in order, pass mark 75');
check($self->lessonLabel($self->lesson('kuiz-self-learning')) === 'KUIZ - 10 SOALAN', 'Kuiz Self-Learning label is KUIZ - 10 SOALAN');
$lr = $self->lockReason($self->lesson('kuiz-self-learning'), $none, $none);
check($lr !== null && array_column($lr['links'], 'key') === ['self-video'], 'Kuiz Self-Learning locked until the video is done (links it)');
check($self->lockReason($self->lesson('self-video'), $none, $none) === null, 'the video is open from the start');
check($self->lockReason($self->lesson('kuiz-self-learning'), ['self-video' => true], $none) === null, 'Kuiz Self-Learning opens after the video');
check($self->effectivePassed(['kuiz-self-learning'], false) === ['kuiz-self-learning' => true], 'self-learning: passing its quiz passes the track');
check($self->effectivePassed(['kuiz-modul-1', 'kuiz-modul-2'], false) === [], 'self-learning: module quiz passes do not count');
check($self->effectivePassed([], true) === ['kuiz-self-learning' => true], 'self-learning: a completion (either track) counts as passed');
check($course->effectivePassed(['kuiz-self-learning'], false) === [], 'e-learning: the self-learning quiz alone does not unlock modules');
check($self->percentDone(['self-video' => true]) === 25 && $course->percentDone([]) === 0, 'percentDone()');
check(format_duration(634) === '10:34' && format_duration(59) === '0:59' && format_duration(3725) === '1:02:05' && format_duration(-5) === '0:00', 'format_duration()');
check(!video_watched_long_enough(null, 634, $now = time()), 'video never opened in this session is not watched');
check(!video_watched_long_enough($now - 316, 634, $now) && video_watched_long_enough($now - 317, 634, $now), 'video counts as watched after duration / VIDEO_MAX_SPEED seconds');

echo $failures === 0 ? "\nAll tests passed.\n" : "\n$failures test(s) failed.\n";
exit($failures === 0 ? 0 : 1);
