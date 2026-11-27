<?php
declare(strict_types=1);

// End-to-end test against a running server (needs the DB from config/config.php).
// Run: & 'C:\xampp 8.2\php\php.exe' tests/e2e.php http://127.0.0.1:8080 [path-to-save-certificate.pdf]
// The migration step uses the mysql client (MYSQL_BIN, default C:\xampp 8.2\mysql\bin\mysql.exe) and a scratch DB <db>_migtest.

require __DIR__ . '/../src/bootstrap.php';

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$jars = [];
$devices = [];
$ch = null;

/** Switch the test browser to device $name (its own cookie jar), creating it on first use. */
function device(string $name): void
{
    global $ch, $devices, $jars;
    if (!isset($devices[$name])) {
        $jar = tempnam(sys_get_temp_dir(), 'nadi_e2e_');
        $jars[] = $jar;
        $h = curl_init();
        curl_setopt_array($h, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR      => $jar,
            CURLOPT_COOKIEFILE     => $jar,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $devices[$name] = $h;
    }
    $ch = $devices[$name];
}
device('main');

/** @return array{status:int, location:string, type:string, body:string} */
function http(string $method, string $path, ?array $fields = null): array
{
    global $ch, $base;
    curl_setopt($ch, CURLOPT_URL, $base . '/' . ltrim($path, '/'));
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields ?? []));
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }
    $raw = curl_exec($ch);
    if ($raw === false) {
        throw new RuntimeException('curl: ' . curl_error($ch));
    }
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($raw, 0, $headerSize);
    $location = preg_match('/^Location:\s*(\S+)/mi', $headers, $m) ? $m[1] : '';
    return [
        'status'   => (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE),
        'location' => $location,
        'type'     => (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
        'body'     => substr($raw, $headerSize),
    ];
}

function csrf_from(string $html): string
{
    return preg_match('/name="csrf" value="([0-9a-f]+)"/', $html, $m) ? $m[1] : '';
}

/** Cookies the test browser holds: name => ['expires' => int (0 = session cookie), 'value' => string]. */
function cookies(): array
{
    global $ch;
    $out = [];
    foreach (curl_getinfo($ch, CURLINFO_COOKIELIST) ?: [] as $line) {
        $f = explode("\t", $line);
        if (count($f) >= 7) {
            $out[$f[5]] = ['expires' => (int)$f[4], 'value' => $f[6]];
        }
    }
    return $out;
}

/** Simulate closing the browser: drop all session cookies, keep persistent ones. */
function close_browser(): void
{
    global $ch;
    curl_setopt($ch, CURLOPT_COOKIELIST, 'SESS');
}

/** Registration fields besides the name and e-mail: a real centre (Johor, Batu Pahat) from content/nadi-centers.csv. */
const E2E_REG_FIELDS = ['age' => '30', 'gender' => 'Perempuan', 'occupation' => 'Usahawan / Peniaga',
    'state' => 'Johor', 'district' => 'Batu Pahat', 'nadi' => 'PEDi_1045'];

/** Register a learner in the test browser; returns the redirect response. */
function register_learner(string $first, string $last, string $email, bool $remember): array
{
    $page = http('GET', 'register.php');
    $fields = [
        'csrf' => csrf_from($page['body']), 'full_name' => "$first $last", 'email' => $email,
    ] + E2E_REG_FIELDS;
    if ($remember) {
        $fields['remember'] = '1';
    }
    return http('POST', 'register.php', $fields);
}

/**
 * Pretend the current device's last request was $secondsAgo seconds ago by editing the dev server's
 * session file (test and server must run on the same machine).
 */
function age_session(int $secondsAgo): bool
{
    $sid = cookies()['nadi_lms_session']['value'] ?? '';
    $file = rtrim((string)session_save_path() ?: sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'sess_' . $sid;
    if ($sid === '' || !is_file($file)) {
        return false;
    }
    $data = (string)file_get_contents($file);
    $data = preg_replace('/last_active\|i:\d+;/', 'last_active|i:' . (time() - $secondsAgo) . ';', $data, 1, $n);
    return $n === 1 && file_put_contents($file, $data) !== false;
}

/** Pretend the video lesson $lessonKey was opened $secondsAgo seconds ago (edits the session file like age_session()). */
function age_video(string $lessonKey, int $secondsAgo): bool
{
    $sid = cookies()['nadi_lms_session']['value'] ?? '';
    $file = rtrim((string)session_save_path() ?: sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'sess_' . $sid;
    if ($sid === '' || !is_file($file)) {
        return false;
    }
    $data = (string)file_get_contents($file);
    $key = 's:' . strlen($lessonKey) . ':"' . $lessonKey . '";i:';
    $data = preg_replace('/' . preg_quote($key, '/') . '\d+;/', $key . (time() - $secondsAgo) . ';', $data, 1, $n);
    return $n === 1 && file_put_contents($file, $data) !== false;
}

/** "Sambung Semula" with an e-mail on the current device; returns the response. */
function resume_with(string $email, bool $remember = false): array
{
    $page = http('GET', 'resume.php');
    $fields = ['csrf' => csrf_from($page['body']), 'login' => $email];
    if ($remember) {
        $fields['remember'] = '1';
    }
    return http('POST', 'resume.php', $fields);
}

/** Mark lessons complete on the current device (as if the learner reached the last slide of each). */
function complete_lessons(array $keys): bool
{
    $ok = true;
    foreach ($keys as $key) {
        $page = http('GET', 'course.php?lesson=' . $key . '&slide=5');
        $r = http('POST', 'lesson-complete.php', ['csrf' => csrf_from($page['body']), 'lesson' => $key]);
        $ok = $ok && $r['status'] === 302 && strpos($r['location'], 'course.php?lesson=') === 0;
    }
    return $ok;
}

function learner_ids_by_email(string $email): array
{
    $stmt = db()->prepare('SELECT id FROM learners WHERE email = ? ORDER BY id');
    $stmt->execute([$email]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/** True when GET $path redirects to the registration page (nobody is signed in). */
function redirects_to_register(string $path): bool
{
    $r = http('GET', $path);
    return $r['status'] === 302 && $r['location'] === 'register.php' && strncmp($r['body'], '%PDF', 4) !== 0;
}

$failures = 0;
function step(int $n, string $label, bool $ok, string $detail = ''): void
{
    global $failures;
    if (!$ok) {
        $failures++;
    }
    echo ($ok ? 'PASS' : 'FAIL') . " step $n: $label" . ($ok || $detail === '' ? '' : " ($detail)") . "\n";
}

$course = Course::load();
$courseKey = $course->key();
/** @var array<int, array> $quizzes module number => quiz (title, pass_mark, questions) */
$quizzes = [];
for ($n = 1; $n <= 5; $n++) {
    $quizzes[$n] = $course->quiz('kuiz-modul-' . $n);
}
$leakFound = false;

/** Answers with the first $correct questions right and the rest wrong. */
function answers_with(array $questions, int $correct): array
{
    $out = [];
    foreach ($questions as $i => $q) {
        if ($i < $correct) {
            $out[$q['id']] = $q['answer'];
        } else {
            $wrong = array_values(array_diff(array_keys($q['options']), [$q['answer']]));
            $out[$q['id']] = $wrong[0];
        }
    }
    return $out;
}

/** True when an open quiz page reveals anything about the correct answers. */
function quiz_page_leaks(string $html, array $questions): bool
{
    foreach ($questions as $q) {
        if (strpos($html, 'name="answers[' . $q['id'] . ']"') === false) {
            return true;
        }
    }
    return strpos($html, 'answer"') !== false || strpos($html, 'checked') !== false;
}

/** Submit answers for one quiz lesson ($quizKey = kuiz-modul-N) on the current device. */
function quiz_post(string $quizKey, array $answers): array
{
    global $leakFound;
    $page = http('GET', 'course.php?lesson=' . $quizKey);
    $owner = Course::forLesson($quizKey);
    if ($page['status'] === 200 && strpos($page['body'], 'quiz-submit.php') !== false && $owner !== null
        && quiz_page_leaks($page['body'], $owner->quiz($quizKey)['questions'])) {
        $leakFound = true;
    }
    // The token is per session; a locked page still has it in the "Selesai / Keluar" form.
    $csrf = csrf_from($page['body']);
    if ($csrf === '') {
        $csrf = csrf_from(http('GET', 'pilih.php')['body']);
    }
    $fields = ['csrf' => $csrf, 'quiz' => $quizKey];
    foreach ($answers as $qid => $opt) {
        $fields["answers[$qid]"] = $opt;
    }
    return http('POST', 'quiz-submit.php', $fields);
}

/** Complete Modul $n's slide lesson and pass Kuiz Modul $n with every answer right. */
function pass_module(int $n): bool
{
    global $quizzes;
    $ok = complete_lessons(["modul-$n-pengenalan"]);
    $r = quiz_post("kuiz-modul-$n", answers_with($quizzes[$n]['questions'], count($quizzes[$n]['questions'])));
    return $ok && $r['status'] === 302 && strpos($r['location'], "course.php?lesson=kuiz-modul-$n&attempt=") === 0;
}

/** "LULUS" / "BELUM LULUS" shown in the sidebar for the quiz lesson titled $title, or '' when absent. */
function sidebar_quiz_status(string $html, string $title): string
{
    $re = '/data-title="' . preg_quote($title, '/') . '">.*?class="lesson-status[^"]*">([A-Z ]+)</s';
    return preg_match($re, $html, $m) ? $m[1] : '';
}

/**
 * Run SQL (a string, or the contents of a file) with the mysql command-line client against $db ('' = none).
 * The client is MYSQL_BIN or the XAMPP default; credentials come from config()['db'].
 * @return array{0:int, 1:string, 2:string} exit code, stdout, stderr
 */
function mysql_run(string $db, string $sqlOrFile): array
{
    $c = config()['db'];
    $cmd = [getenv('MYSQL_BIN') ?: 'C:\xampp 8.2\mysql\bin\mysql.exe', '--no-defaults', '--batch',
        '-h', (string)$c['host'], '-P', (string)($c['port'] ?? 3306), '-u', (string)$c['user']];
    if ((string)$c['pass'] !== '') {
        $cmd[] = '-p' . $c['pass'];
    }
    if ($db !== '') {
        $cmd[] = $db;
    }
    $sql = is_file($sqlOrFile) ? (string)file_get_contents($sqlOrFile) : $sqlOrFile;
    $proc = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($proc)) {
        return [-1, '', 'proc_open failed'];
    }
    fwrite($pipes[0], $sql);
    fclose($pipes[0]);
    $out = (string)stream_get_contents($pipes[1]);
    $err = (string)stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($proc), $out, $err];
}

function latest_attempt(int $learnerId): ?array
{
    $stmt = db()->prepare('SELECT * FROM quiz_attempts WHERE learner_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$learnerId]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function completion_count(int $learnerId): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM completions WHERE learner_id = ?');
    $stmt->execute([$learnerId]);
    return (int)$stmt->fetchColumn();
}

/** Insert a passed attempt of the old single final quiz (as left by the old portal, quiz_key 'kuiz-akhir'). */
function legacy_attempt_create(int $learnerId, string $courseKey): int
{
    db()->prepare(
        "INSERT INTO quiz_attempts (learner_id, course_key, quiz_key, correct_count, total_questions, score_percent, passed, answers_json, created_at)
         VALUES (?, ?, 'kuiz-akhir', 9, 10, 90.00, 1, '{}', ?)"
    )->execute([$learnerId, $courseKey, now_str()]);
    return (int)db()->lastInsertId();
}

try {
    // 1. Landing page.
    $r = http('GET', '/');
    step(1, 'GET / returns 200 with Daftar Sekarang and the per-module quiz text',
        $r['status'] === 200 && strpos($r['body'], 'Daftar Sekarang') !== false
        && strpos($r['body'], 'lulus kuiz di hujung setiap modul') !== false, "status {$r['status']}");

    // 2. Registration.
    $suffix = (string)time();
    $first = 'Ujian';
    $last = 'E2E' . $suffix;
    $email = 'e2e+' . $suffix . '@example.com';
    $page = http('GET', 'register.php');
    $r = http('POST', 'register.php', [
        'csrf' => csrf_from($page['body']), 'full_name' => "  $first   $last ", 'email' => $email,
    ] + E2E_REG_FIELDS);
    $stmt = db()->prepare('SELECT * FROM learners WHERE email = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$email]);
    $learner = $stmt->fetch() ?: null;
    $choose = http('GET', 'pilih.php');
    step(2, 'registration saves the learner and redirects to pilih.php, which offers E-Learning and Self-Learning',
        $r['status'] === 302 && $r['location'] === 'pilih.php' && $learner !== null
        && $learner['full_name'] === "$first $last" && $learner['first_name'] === ''
        && $learner['gender'] === 'Perempuan' && $learner['occupation'] === 'Usahawan / Peniaga'
        && $learner['state'] === 'Johor' && $learner['district'] === 'Batu Pahat'
        && $learner['nadi_code'] === 'PEDi_1045' && $learner['nadi_name'] === nadi_district_centers('Johor', 'Batu Pahat')['PEDi_1045']
        && $learner['phone'] === '' && $learner['address'] === ''
        && $choose['status'] === 200 && strpos($choose['body'], 'Pilih cara belajar anda') !== false
        && strpos($choose['body'], 'href="course.php?track=e-learning"') !== false
        && strpos($choose['body'], 'href="course.php?track=self-learning"') !== false
        && strpos($choose['body'], 'action="reset.php"') !== false,
        "status {$r['status']} location {$r['location']} pilih {$choose['status']}");
    if ($learner === null) {
        throw new RuntimeException('no learner row, cannot continue');
    }
    $learnerId = (int)$learner['id'];

    // 3. Kuiz Modul 1 is locked before the Modul 1 lessons are done.
    $page = http('GET', 'course.php?lesson=kuiz-modul-1');
    $gateShown = $page['status'] === 403 && strpos($page['body'], 'Sila lengkapkan semua pelajaran dalam Modul 1') !== false
        && strpos($page['body'], 'quiz-submit.php') === false;
    $r = quiz_post('kuiz-modul-1', answers_with($quizzes[1]['questions'], 3));
    step(3, 'Kuiz Modul 1 locked before Modul 1 lessons (403 lock page without quiz form, submit 403)',
        $gateShown && $r['status'] === 403, "page status {$page['status']} submit status {$r['status']}");

    // 4. Modul 2 is refused before Kuiz Modul 1 is passed.
    $page = http('GET', 'course.php?lesson=modul-2-pengenalan');
    $r = http('POST', 'lesson-complete.php', ['csrf' => csrf_from($page['body']), 'lesson' => 'modul-2-pengenalan']);
    step(4, 'Modul 2 locked before Kuiz Modul 1 pass (403 "Modul ini dikunci", lesson-complete 403, no progress)',
        $page['status'] === 403 && strpos($page['body'], 'Modul ini dikunci. Sila lulus Kuiz Modul 1 terlebih dahulu.') !== false
        && strpos($page['body'], 'class="slide"') === false && strpos($page['body'], 'is-locked') !== false
        && $r['status'] === 403 && !in_array('modul-2-pengenalan', progress_keys($learnerId), true),
        "page {$page['status']} complete {$r['status']}");

    // 5. Viewer navigation and Modul 1 lesson completion.
    $m1 = count($course->lesson('modul-1-pengenalan')['slides']);
    $s1 = http('GET', 'course.php?lesson=modul-1-pengenalan&slide=1');
    $sN = http('GET', 'course.php?lesson=modul-1-pengenalan&slide=' . $m1);
    $r = http('POST', 'lesson-complete.php', ['csrf' => csrf_from($sN['body']), 'lesson' => 'modul-1-pengenalan']);
    step(5, "viewer 1/$m1 -> $m1/$m1 and completing Modul 1 leads to Kuiz Modul 1",
        strpos($s1['body'], ">1/$m1<") !== false && strpos($sN['body'], ">$m1/$m1<") !== false
        && $r['status'] === 302 && $r['location'] === 'course.php?lesson=kuiz-modul-1',
        "status {$r['status']} location {$r['location']}");

    // 6. Completion refused before passing; the quiz page shows no correct answers.
    $page = http('GET', 'course.php?lesson=kuiz-modul-1');
    $r = http('POST', 'complete.php', ['csrf' => csrf_from($page['body'])]);
    $leak = quiz_page_leaks($page['body'], $quizzes[1]['questions']);
    step(6, 'Kuiz Modul 1 opens (3 questions, no answers in HTML); complete.php before passing creates no completion',
        $page['status'] === 200 && strpos($page['body'], 'name="quiz" value="kuiz-modul-1"') !== false && !$leak
        && completion_count($learnerId) === 0 && $r['status'] === 302 && $r['location'] === 'course.php?lesson=kuiz-modul-1',
        "status {$r['status']} location {$r['location']} leak=" . ($leak ? 'yes' : 'no'));

    // 7. Failing attempt on Kuiz Modul 1 (2/3).
    $r = quiz_post('kuiz-modul-1', answers_with($quizzes[1]['questions'], 2));
    $a = latest_attempt($learnerId);
    $failId = (int)($a['id'] ?? 0);
    $res = $r['location'] !== '' ? http('GET', $r['location']) : ['body' => ''];
    step(7, 'Kuiz Modul 1 2/3 stored (quiz_key kuiz-modul-1, passed=0), shows GAGAL + Cuba Lagi and a review (wrong question explained, correct answer hidden)',
        $a !== null && $a['quiz_key'] === 'kuiz-modul-1' && (int)$a['correct_count'] === 2 && (int)$a['passed'] === 0
        && strpos($res['body'], 'Markah anda: 2/3 (66.67%)') !== false
        && strpos($res['body'], 'GAGAL') !== false && strpos($res['body'], 'Cuba Lagi') !== false
        && !in_array('kuiz-modul-1', progress_keys($learnerId), true)
        // Semakan Jawapan: 2 right, q3 wrong with its explanation and a link to study, but not the correct answer.
        && strpos($res['body'], 'Semakan Jawapan') !== false && strpos($res['body'], 'Betul 2 daripada 3.') !== false
        && substr_count($res['body'], 'review-item is-wrong') === 1
        && strpos($res['body'], htmlspecialchars($quizzes[1]['questions'][2]['explanation'], ENT_QUOTES)) !== false
        && strpos($res['body'], 'Jawapan betul:') === false
        && strpos($res['body'], htmlspecialchars($quizzes[1]['questions'][2]['options'][$quizzes[1]['questions'][2]['answer']], ENT_QUOTES)) === false
        && strpos($res['body'], 'Ulang kaji:') !== false && strpos($res['body'], 'href="course.php?lesson=modul-1-pengenalan"') !== false,
        "location {$r['location']}");

    // 8. Retry passes; Modul 2 opens.
    $r = quiz_post('kuiz-modul-1', answers_with($quizzes[1]['questions'], 3));
    $a = latest_attempt($learnerId);
    $res = $r['location'] !== '' ? http('GET', $r['location']) : ['body' => ''];
    $m2 = http('GET', 'course.php?lesson=modul-2-pengenalan');
    $c2 = http('POST', 'lesson-complete.php', ['csrf' => csrf_from($m2['body']), 'lesson' => 'modul-2-pengenalan']);
    $wrongQuiz = http('GET', 'course.php?lesson=kuiz-modul-2&attempt=' . $failId);
    step(8, 'Kuiz Modul 1 retry 3/3 LULUS (no completion form, sidebar LULUS); Modul 2 now 200 and completable',
        $a !== null && $a['quiz_key'] === 'kuiz-modul-1' && (int)$a['passed'] === 1
        && strpos($res['body'], 'Markah anda: 3/3 (100%)') !== false && strpos($res['body'], 'LULUS') !== false
        && strpos($res['body'], 'Tahniah! Anda lulus kuiz ini.') !== false
        && strpos($res['body'], 'Teruskan ke Asas AI') !== false
        && strpos($res['body'], 'action="complete.php"') === false
        && sidebar_quiz_status($res['body'], 'Kuiz Modul 1') === 'LULUS'
        && sidebar_quiz_status($res['body'], 'Kuiz Modul 2') === 'BELUM LULUS'
        && in_array('kuiz-modul-1', progress_keys($learnerId), true)
        && $m2['status'] === 200 && $c2['status'] === 302 && $c2['location'] === 'course.php?lesson=kuiz-modul-2'
        && $wrongQuiz['status'] === 404,
        "modul-2 {$m2['status']} complete {$c2['status']} other-quiz attempt {$wrongQuiz['status']}");

    // 9. Pass Modul 2-4; while Kuiz Modul 5 is unpassed nothing completes the course.
    $ok = pass_module(2) && pass_module(3) && pass_module(4) && complete_lessons(['modul-5-pengenalan']);
    $page = http('GET', 'course.php?lesson=kuiz-modul-5');
    $done = http('POST', 'complete.php', ['csrf' => csrf_from($page['body'])]);
    $cert = http('GET', 'certificate.php');
    $pdf = http('GET', 'certificate-download.php');
    $pen = http('GET', 'course.php?lesson=penutup');
    step(9, 'after Kuiz Modul 1-4: complete.php, certificate and download refused, Penutup 403 while Kuiz Modul 5 unpassed',
        $ok && $page['status'] === 200 && completion_count($learnerId) === 0
        && $done['status'] === 302 && $done['location'] === 'course.php?lesson=kuiz-modul-5'
        && $cert['status'] === 302 && $cert['location'] === 'course.php?lesson=kuiz-modul-5'
        && $pdf['status'] === 302 && strncmp($pdf['body'], '%PDF', 4) !== 0
        && $pen['status'] === 403 && strpos($pen['body'], 'Penutup dibuka selepas anda lulus kesemua kuiz modul') !== false,
        "complete {$done['location']} cert {$cert['status']} pdf {$pdf['status']} penutup {$pen['status']}");

    // 10. Pass Kuiz Modul 5, then LENGKAP & TERUSKAN creates the completion.
    $r = quiz_post('kuiz-modul-5', answers_with($quizzes[5]['questions'], 2));
    $res = $r['location'] !== '' ? http('GET', $r['location']) : ['body' => ''];
    $r = http('POST', 'complete.php', ['csrf' => csrf_from($res['body'])]);
    $completion = completion_find($learnerId, $courseKey);
    $code = $completion['certificate_code'] ?? '';
    $pen = http('GET', 'course.php?lesson=penutup');
    step(10, 'Kuiz Modul 5 pass offers LENGKAP & TERUSKAN; complete.php creates a NADI-XXXXXXXXXX code; Penutup opens; no answers leaked',
        strpos($res['body'], 'action="complete.php"') !== false
        && $completion !== null && preg_match('/^NADI-[0-9A-F]{10}$/', $code) === 1
        && $r['status'] === 302 && $r['location'] === 'certificate.php' && $pen['status'] === 200 && !$leakFound,
        "status {$r['status']} location {$r['location']} code $code leak=" . ($leakFound ? 'yes' : 'no'));

    // 11. Certificate PDF.
    $cert = http('GET', 'certificate.php');
    $pdf = http('GET', 'certificate-download.php');
    $today = format_date_ms(date('Y-m-d H:i:s'));
    step(11, 'certificate PDF has the name, today\'s date and the code',
        strpos($cert['body'], 'Tahniah!') !== false
        && strpos($pdf['type'], 'application/pdf') === 0 && strncmp($pdf['body'], '%PDF', 4) === 0
        && strpos($pdf['body'], "$first $last") !== false && strpos($pdf['body'], $today) !== false
        && $code !== '' && strpos($pdf['body'], $code) !== false,
        "type {$pdf['type']} date $today");
    if (isset($argv[2]) && $argv[2] !== '') {
        file_put_contents($argv[2], $pdf['body']);
    }

    // 12. Public verification.
    $v = http('GET', 'verify.php?code=' . rawurlencode(strtolower($code)));
    step(12, 'verify.php shows Sijil sah with the name',
        strpos($v['body'], 'Sijil sah') !== false && strpos($v['body'], "$first $last") !== false);

    // Shared computer: learner A (above) registered without "Ingat saya", finished and now leaves.
    // 13. No persistent learner cookie, and the certificate page offers "Selesai / Keluar".
    $jarNow = cookies();
    step(13, 'A has no persistent learner cookie and the certificate page shows Selesai / Keluar',
        !isset($jarNow[LEARNER_COOKIE]) && strpos($cert['body'], 'Selesai / Keluar') !== false
        && strpos($cert['body'], 'action="reset.php"') !== false,
        'cookies: ' . implode(',', array_keys($jarNow)));

    // 14. A presses "Selesai / Keluar".
    $r = http('POST', 'reset.php', ['csrf' => csrf_from($cert['body'])]);
    step(14, 'Selesai / Keluar signs A out and redirects to index.php?keluar=1',
        $r['status'] === 302 && $r['location'] === 'index.php?keluar=1', "status {$r['status']} location {$r['location']}");

    // 15. The next person on the same browser sees nothing of A.
    $bye = http('GET', $r['location']);
    $home = http('GET', '/');
    $reg = http('GET', 'register.php');
    step(15, 'next visitor sees no name or progress of A and cannot open or download A\'s certificate',
        strpos($bye['body'], 'Anda telah keluar') !== false
        && strpos($home['body'], 'Daftar Sekarang') !== false
        && strpos($home['body'] . $bye['body'] . $reg['body'], $last) === false
        && strpos($reg['body'], $email) === false
        && redirects_to_register('course.php') && redirects_to_register('course.php?lesson=kuiz-modul-1')
        && redirects_to_register('certificate.php') && redirects_to_register('certificate-download.php'));

    // 16. Shared computer, learner leaves without pressing Keluar and the browser is closed.
    $lastB = 'E2EB' . $suffix;
    register_learner('Ujian', $lastB, 'e2e-b+' . $suffix . '@example.com', false);
    $before = http('GET', '/');
    close_browser();
    $after = http('GET', '/');
    step(16, 'without Keluar, closing the browser forgets a learner who did not tick Ingat saya',
        strpos($before['body'], $lastB) !== false && strpos($after['body'], $lastB) === false
        && strpos($after['body'], 'Daftar Sekarang') !== false && redirects_to_register('certificate-download.php'));

    // 17. Shared computer, learner walks away and the browser stays open: idle timeout.
    $lastC = 'E2EC' . $suffix;
    register_learner('Ujian', $lastC, 'e2e-c+' . $suffix . '@example.com', false);
    $before = http('GET', '/');
    $aged = age_session(LEARNER_IDLE_MINUTES * 60 + 60);
    $after = http('GET', '/');
    step(17, 'after ' . LEARNER_IDLE_MINUTES . ' idle minutes the learner is signed out',
        $aged && strpos($before['body'], $lastC) !== false && strpos($after['body'], $lastC) === false
        && redirects_to_register('course.php'),
        $aged ? '' : 'could not age the session file');

    // 18. Own device: "Ingat saya" keeps the learner across a browser restart until "Bukan anda? Mula semula".
    $lastD = 'E2ED' . $suffix;
    register_learner('Ujian', $lastD, 'e2e-d+' . $suffix . '@example.com', true);
    $token = cookies()[LEARNER_COOKIE] ?? null;
    close_browser();
    $home = http('GET', '/');
    $remembered = $token !== null && $token['expires'] > time() + (LEARNER_COOKIE_DAYS - 1) * 86400
        && strpos($home['body'], 'Selamat kembali') !== false && strpos($home['body'], $lastD) !== false;
    $r = http('POST', 'reset.php', ['csrf' => csrf_from($home['body'])]);
    close_browser();
    $home = http('GET', '/');
    step(18, 'Ingat saya keeps the learner for ' . LEARNER_COOKIE_DAYS . ' days; Mula semula removes the cookie',
        $remembered && $r['status'] === 302 && !isset(cookies()[LEARNER_COOKIE])
        && strpos($home['body'], $lastD) === false && strpos($home['body'], 'Daftar Sekarang') !== false);

    // 19. A remembered learner's cookie does not survive the next person registering without "Ingat saya".
    $lastE = 'E2EE' . $suffix;
    $lastF = 'E2EF' . $suffix;
    register_learner('Ujian', $lastE, 'e2e-e+' . $suffix . '@example.com', true);
    register_learner('Ujian', $lastF, 'e2e-f+' . $suffix . '@example.com', false);
    $c = cookies()[LEARNER_COOKIE] ?? null;
    $hadCookie = $c !== null && $c['expires'] > time(); // curl may keep a deleted cookie as already expired
    close_browser();
    $home = http('GET', '/');
    step(19, 'registering without Ingat saya clears an earlier learner\'s remember cookie',
        !$hadCookie && strpos($home['body'], $lastE) === false && strpos($home['body'], $lastF) === false,
        'cookie=' . ($hadCookie ? 'yes' : 'no') . ' E=' . (strpos($home['body'], $lastE) !== false ? 'shown' : 'hidden')
        . ' F=' . (strpos($home['body'], $lastF) !== false ? 'shown' : 'hidden'));

    // "Sambung Semula" (resume by e-mail). Rate-limit rows from earlier local runs would get in the way.
    $localIp = hash('sha256', '127.0.0.1');
    db()->prepare('DELETE FROM resume_attempts WHERE ip_hash = ?')->execute([$localIp]);
    $allLessons = count($course->lessons());

    // 20. Device 1: learner G registers without "Ingat saya", passes Modul 1, completes the Modul 2 lesson, presses Keluar.
    device('device-1');
    $lastG = 'E2EG' . $suffix;
    $emailG = 'e2e-g+' . $suffix . '@example.com';
    register_learner('Ujian', $lastG, $emailG, false);
    $ok = pass_module(1) && complete_lessons(['modul-2-pengenalan']);
    $page = http('GET', 'course.php');
    $r = http('POST', 'reset.php', ['csrf' => csrf_from($page['body'])]);
    $idsG = learner_ids_by_email($emailG);
    step(20, 'device 1: G passes Kuiz Modul 1 and completes Modul 2 lesson (3 lessons), the course page has Selesai / Keluar, G signs out',
        $ok && count($idsG) === 1 && count(progress_keys($idsG[0])) === 3
        && strpos($page['body'], 'Selesai / Keluar') !== false && $r['location'] === 'index.php?keluar=1');

    // 21. Device 2 (fresh browser): Sambung Semula with G's e-mail (different letter case) restores progress and quiz status.
    device('device-2');
    $home = http('GET', '/');
    $r = resume_with(strtoupper($emailG));
    $choose = http('GET', 'pilih.php');
    $page = http('GET', 'course.php');
    $pct = (int)round(3 * 100 / $allLessons);
    step(21, 'device 2: Sambung Semula restores G\'s progress (' . $pct . '%), Kuiz Modul 1 LULUS, Kuiz Modul 2 not passed (no remember cookie)',
        strpos($home['body'], 'Sambung Semula') !== false && strpos($home['body'], 'action="resume.php"') !== false
        && $r['status'] === 302 && $r['location'] === 'pilih.php'
        && strpos($choose['body'], 'Kemajuan anda telah disambung semula') !== false
        && strpos($choose['body'], $pct . '% selesai') !== false
        && strpos($page['body'], $lastG) !== false
        && strpos($page['body'], 'progress-text">' . $pct . '% selesai') !== false
        && sidebar_quiz_status($page['body'], 'Kuiz Modul 1') === 'LULUS'
        && sidebar_quiz_status($page['body'], 'Kuiz Modul 2') === 'BELUM LULUS'
        && !isset(cookies()[LEARNER_COOKIE]),
        "status {$r['status']} location {$r['location']}");

    // 22. G finishes Modul 2-5 on device 2 and downloads the certificate with G's name.
    $ok = pass_module(2) && pass_module(3) && pass_module(4) && pass_module(5);
    $page = http('GET', 'course.php?lesson=kuiz-modul-5');
    $r = http('POST', 'complete.php', ['csrf' => csrf_from($page['body'])]);
    $pdf = http('GET', 'certificate-download.php');
    step(22, 'device 2: G passes Kuiz Modul 2-5, completes and downloads a certificate with G\'s name',
        $ok && $r['location'] === 'certificate.php' && strncmp($pdf['body'], '%PDF', 4) === 0
        && strpos($pdf['body'], 'Ujian ' . $lastG) !== false && completion_find($idsG[0], $courseKey) !== null);

    // 23. Unknown e-mail: Malay not-found message with a link to registration.
    device('device-3');
    $r = resume_with('tiada-' . $suffix . '@example.com');
    step(23, 'unknown e-mail shows the Malay not-found message and offers registration',
        $r['status'] === 404 && strpos($r['body'], 'Tiada peserta berdaftar dengan nama atau e-mel ini') !== false
        && strpos($r['body'], 'href="register.php"') !== false && redirects_to_register('course.php'),
        "status {$r['status']}");

    // 24. Registering again with G's e-mail resumes G (no duplicate row; G's name stays on the certificate).
    $r = register_learner('Lain', 'Orang' . $suffix, $emailG, false);
    $page = http('GET', 'pilih.php');
    $cert = http('GET', 'certificate.php');
    step(24, 're-registering with an existing e-mail resumes that learner without creating a duplicate',
        $r['status'] === 302 && $r['location'] === 'pilih.php' && learner_ids_by_email($emailG) === $idsG
        && strpos($page['body'], 'E-mel ini sudah berdaftar') !== false
        && strpos($cert['body'], 'Ujian ' . $lastG) !== false && strpos($cert['body'], 'Orang' . $suffix) === false);

    // 25. Duplicate e-mails left by earlier testing: the record with the most progress is resumed.
    $emailDup = 'e2e-dup+' . $suffix . '@example.com';
    $dupFields = ['age' => 30, 'email' => $emailDup, 'phone' => '0123456789', 'address' => 'Ujian', 'occupation' => 'Penguji'];
    $older = learner_create(['full_name' => 'Lama Dup' . $suffix] + $dupFields, hash('sha256', random_bytes(32)));
    $newer = learner_create(['full_name' => 'Baru Dup' . $suffix] + $dupFields, hash('sha256', random_bytes(32)));
    progress_mark($older, 'modul-1-pengenalan');
    progress_mark($older, 'modul-2-pengenalan');
    progress_mark($newer, 'modul-1-pengenalan');
    device('device-4');
    resume_with($emailDup);
    $page = http('GET', 'course.php');
    $pickMost = strpos($page['body'], 'Lama Dup' . $suffix) !== false;
    progress_mark($newer, 'modul-2-pengenalan');
    device('device-5');
    resume_with($emailDup);
    $page = http('GET', 'course.php');
    $tieNewest = strpos($page['body'], 'Baru Dup' . $suffix) !== false;
    step(25, 'duplicate e-mail: the learner with the most progress is resumed, the newest on a tie; no rows removed',
        $pickMost && $tieNewest && learner_ids_by_email($emailDup) === [$older, $newer]);

    // 26. Long reading: more than the idle timeout inside one module, with slide activity, keeps the learner.
    device('reader');
    $lastH = 'E2EH' . $suffix;
    register_learner('Ujian', $lastH, 'e2e-h+' . $suffix . '@example.com', false);
    $ok = pass_module(1) && pass_module(2) && pass_module(3);
    $page = http('GET', 'course.php?lesson=modul-4-pengenalan&slide=1');
    $csrf = csrf_from($page['body']);
    $ok = $ok && $page['status'] === 200 && strpos($page['body'], 'assets/js/course.js') !== false;
    for ($i = 0; $i < 3; $i++) {
        $ok = $ok && age_session(20 * 60); // 20 minutes on slides without a page request
        $ping = http('POST', 'keepalive.php', ['csrf' => $csrf]);
        $ok = $ok && $ping['status'] === 204;
    }
    $ok = $ok && age_session(20 * 60);
    $r = http('POST', 'lesson-complete.php', ['csrf' => $csrf, 'lesson' => 'modul-4-pengenalan']);
    $idH = learner_ids_by_email('e2e-h+' . $suffix . '@example.com')[0] ?? 0;
    device('no-session');
    $anon = http('POST', 'keepalive.php', ['csrf' => csrf_from(http('GET', 'resume.php')['body'])]);
    step(26, '80 minutes reading Modul 4 (after Kuiz Modul 1-3) with keep-alive pings: lesson-complete still saves the progress',
        $ok && $r['status'] === 302 && $r['location'] === 'course.php?lesson=kuiz-modul-4'
        && in_array('modul-4-pengenalan', progress_keys($idH), true) && $anon['status'] === 401,
        "complete status {$r['status']} location {$r['location']} anon keepalive {$anon['status']}");

    // 27. "Ingat saya" on two own devices: resuming with the box ticked on device B keeps device A remembered.
    device('phone');
    $lastI = 'E2EI' . $suffix;
    $emailI = 'e2e-i+' . $suffix . '@example.com';
    register_learner('Ujian', $lastI, $emailI, true);
    device('laptop');
    resume_with($emailI, true);
    $laptopCookie = cookies()[LEARNER_COOKIE] ?? null;
    close_browser();
    $laptop = http('GET', '/');
    device('phone');
    close_browser();
    $phone = http('GET', '/');
    step(27, 'Sambung Semula with Ingat saya sets a 30-day cookie and the first device stays remembered',
        $laptopCookie !== null && $laptopCookie['expires'] > time() + (LEARNER_COOKIE_DAYS - 1) * 86400
        && strpos($laptop['body'], $lastI) !== false && strpos($phone['body'], $lastI) !== false);

    // 28. Rate limit: after RESUME_MAX_PER_EMAIL tries for one e-mail, the next one is refused with 429.
    device('attacker');
    $target = 'e2e-rl+' . $suffix . '@example.com';
    $statuses = [];
    for ($i = 0; $i <= RESUME_MAX_PER_EMAIL; $i++) {
        $statuses[] = resume_with($target)['status'];
    }
    $last = array_pop($statuses);
    $r = resume_with($target);
    step(28, 'Sambung Semula is rate limited (' . RESUME_MAX_PER_EMAIL . ' tries per e-mail per '
        . RESUME_WINDOW_MINUTES . ' minutes)',
        array_unique($statuses) === [404] && $last === 429 && strpos($r['body'], 'Terlalu banyak cubaan') !== false,
        'statuses ' . implode(',', $statuses) . " then $last");
    db()->prepare('DELETE FROM resume_attempts WHERE ip_hash = ?')->execute([$localIp]);

    // 29. Legacy learner: passed the old Kuiz Akhir (quiz_key 'kuiz-akhir') but never pressed LENGKAP & TERUSKAN.
    $emailJ = 'e2e-j+' . $suffix . '@example.com';
    $idJ = learner_create(['full_name' => 'Lama Akhir' . $suffix, 'age' => 40, 'email' => $emailJ,
        'phone' => '0123456789', 'address' => 'Ujian', 'occupation' => 'Penguji'], hash('sha256', random_bytes(32)));
    for ($n = 1; $n <= 5; $n++) {
        progress_mark($idJ, "modul-$n-pengenalan");
    }
    legacy_attempt_create($idJ, $courseKey);
    device('legacy-j');
    resume_with($emailJ);
    $m5 = http('GET', 'course.php?lesson=modul-5-pengenalan');
    $pen = http('GET', 'course.php?lesson=penutup');
    $r = http('POST', 'complete.php', ['csrf' => csrf_from($pen['body'])]);
    $cJ = completion_find($idJ, $courseKey);
    $v = $cJ !== null ? http('GET', 'verify.php?code=' . rawurlencode($cJ['certificate_code'])) : ['body' => ''];
    step(29, 'legacy kuiz-akhir pass: Modul 5 and Penutup open, complete.php creates a completion, verify shows Sijil sah',
        $m5['status'] === 200 && $pen['status'] === 200 && sidebar_quiz_status($pen['body'], 'Kuiz Modul 3') === 'LULUS'
        && $r['status'] === 302 && $r['location'] === 'certificate.php' && $cJ !== null
        && strpos($v['body'], 'Sijil sah') !== false && strpos($v['body'], 'Lama Akhir' . $suffix) !== false,
        "modul-5 {$m5['status']} penutup {$pen['status']} complete {$r['location']}");

    // 30. Legacy learner with an old completion downloads the certificate (code and date unchanged).
    $emailK = 'e2e-k+' . $suffix . '@example.com';
    $idK = learner_create(['full_name' => 'Lama Selesai' . $suffix, 'age' => 40, 'email' => $emailK,
        'phone' => '0123456789', 'address' => 'Ujian', 'occupation' => 'Penguji'], hash('sha256', random_bytes(32)));
    $cK = completion_create($idK, $courseKey, legacy_attempt_create($idK, $courseKey));
    device('legacy-k');
    resume_with($emailK);
    $pdf = http('GET', 'certificate-download.php');
    $q1 = http('GET', 'course.php?lesson=kuiz-modul-1');
    $cK2 = completion_find($idK, $courseKey);
    step(30, 'legacy completion: certificate downloads with the original code, quizzes show LULUS',
        strncmp($pdf['body'], '%PDF', 4) === 0 && strpos($pdf['body'], $cK['certificate_code']) !== false
        && sidebar_quiz_status($q1['body'], 'Kuiz Modul 5') === 'LULUS' && $cK2 == $cK,
        "pdf status {$pdf['status']}");

    // 31. Migration on a scratch copy in the old format: imports twice, old rows become 'kuiz-akhir', completions intact.
    $scratch = config()['db']['name'] . '_migtest';
    $ok = true;
    $detail = [];
    [$x, , $err] = mysql_run('', "DROP DATABASE IF EXISTS `$scratch`; CREATE DATABASE `$scratch` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $ok = $ok && $x === 0;
    [$x, , $err2] = mysql_run($scratch, APP_ROOT . '/sql/schema.sql');
    $ok = $ok && $x === 0;
    [$x, , $err3] = mysql_run($scratch,
        "ALTER TABLE quiz_attempts DROP INDEX idx_attempts_learner_quiz, DROP COLUMN quiz_key;
         INSERT INTO learners (id, first_name, last_name, age, email, phone, address, occupation, token_hash, created_at)
             VALUES (1, 'Lama', 'Migrasi', 40, 'mig@example.com', '0123456789', 'Ujian', 'Penguji', REPEAT('a', 64), '2026-01-02 03:04:05');
         INSERT INTO quiz_attempts (id, learner_id, course_key, correct_count, total_questions, score_percent, passed, answers_json, created_at)
             VALUES (1, 1, 'ai-essentials', 8, 10, 80.00, 1, '{}', '2026-01-02 03:10:00');
         INSERT INTO completions (id, learner_id, course_key, quiz_attempt_id, certificate_code, completed_at)
             VALUES (1, 1, 'ai-essentials', 1, 'NADI-00000000AA', '2026-01-02 03:11:00');");
    $ok = $ok && $x === 0;
    $detail[] = trim($err . $err2 . $err3);
    $exits = [];
    for ($i = 0; $i < 2; $i++) {
        [$x, , $e] = mysql_run($scratch, APP_ROOT . '/sql/migrate-module-quizzes.sql');
        $exits[] = $x;
        if ($e !== '') {
            $detail[] = trim($e);
        }
    }
    $c = config()['db'];
    $pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int)($c['port'] ?? 3306), $scratch),
        $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $col = $pdo->query("SHOW COLUMNS FROM quiz_attempts LIKE 'quiz_key'")->fetch();
    $idx = $pdo->query("SHOW INDEX FROM quiz_attempts WHERE Key_name = 'idx_attempts_learner_quiz'")->fetchAll();
    $row = $pdo->query('SELECT quiz_key, correct_count, passed FROM quiz_attempts WHERE id = 1')->fetch();
    $comp = $pdo->query('SELECT learner_id, quiz_attempt_id, certificate_code, completed_at FROM completions')->fetchAll();
    $pdo = null;
    [$x] = mysql_run('', "DROP DATABASE IF EXISTS `$scratch`;");
    step(31, "sql/migrate-module-quizzes.sql imports twice on old-format data ($scratch): column + index added, old attempt 'kuiz-akhir', completion intact",
        $ok && $exits === [0, 0] && $col !== false && $col['Default'] === 'kuiz-akhir' && count($idx) === 2
        && $row !== false && $row['quiz_key'] === 'kuiz-akhir' && (int)$row['passed'] === 1
        && $comp == [['learner_id' => 1, 'quiz_attempt_id' => 1, 'certificate_code' => 'NADI-00000000AA', 'completed_at' => '2026-01-02 03:11:00']]
        && $x === 0,
        'exits ' . implode(',', $exits) . ' ' . implode(' | ', array_filter($detail)));

    // Self-Learning track: watch the video to the end -> Kuiz Self-Learning -> the same certificate.
    $self = Course::load('self-learning');
    $video = $self->lesson('self-video');
    $selfQuiz = $self->quiz('kuiz-self-learning');
    // 32. Learner S picks Self-Learning: the video page shows a poster (no iframe yet) and a disabled button.
    device('self');
    $lastS = 'E2ES' . $suffix;
    $emailS = 'e2e-s+' . $suffix . '@example.com';
    register_learner('Ujian', $lastS, $emailS, false);
    $idS = learner_ids_by_email($emailS)[0] ?? 0;
    $intro = http('GET', 'course.php?track=self-learning');
    $outroLocked = http('GET', 'course.php?lesson=self-penutup');
    $page = http('GET', 'course.php?lesson=self-video');
    $quizLocked = http('GET', 'course.php?lesson=kuiz-self-learning');
    step(32, 'Self-Learning opens at the Pengenalan video; Penutup locked until Kuiz Self-Learning; main video: poster, no iframe, LENGKAP disabled; quiz locked',
        $intro['status'] === 200 && strpos($intro['body'], 'data-youtube="' . $self->lesson('self-pengenalan')['youtube'] . '"') !== false
        && $outroLocked['status'] === 403 && strpos($outroLocked['body'], 'Penutup dibuka selepas anda lulus Kuiz Self-Learning.') !== false
        && $page['status'] === 200 && strpos($page['body'], 'id="video-lesson"') !== false
        && strpos($page['body'], 'data-youtube="' . $video['youtube'] . '"') !== false
        && strpos($page['body'], 'id="video-play"') !== false && strpos($page['body'], '<iframe') === false
        && preg_match('/id="complete-btn"\s+disabled/', $page['body']) === 1
        && strpos($page['body'], 'class="track-switch-btn is-active" href="course.php?track=self-learning"') !== false
        && strpos($page['body'], 'VIDEO - ' . format_duration((int)$video['duration'])) !== false
        && $quizLocked['status'] === 403 && strpos($quizLocked['body'], 'Sila lengkapkan semua pelajaran dalam Modul 1: Video Kursus') !== false,
        "video {$page['status']} quiz {$quizLocked['status']}");

    // 33. Completing the video right away is refused; so are the certificate and complete.php.
    $csrfS = csrf_from($page['body']);
    $early = http('POST', 'lesson-complete.php', ['csrf' => $csrfS, 'lesson' => 'self-video']);
    $cert = http('GET', 'certificate.php');
    $done = http('POST', 'complete.php', ['csrf' => $csrfS, 'track' => 'self-learning']);
    step(33, 'video not watched: lesson-complete 403, certificate.php and complete.php go to Kuiz Self-Learning',
        $early['status'] === 403 && strpos($early['body'], 'Sila tonton video sekurang-kurangnya 10 saat') !== false
        && strpos($page['body'], 'data-min-watch="10"') !== false && strpos($page['body'], 'Ditonton 0 / 10 saat') !== false
        && !in_array('self-video', progress_keys($idS), true)
        && $cert['status'] === 302 && $cert['location'] === 'course.php?lesson=kuiz-self-learning'
        && $done['status'] === 302 && $done['location'] === 'course.php?lesson=kuiz-self-learning' && completion_count($idS) === 0,
        "early {$early['status']} cert {$cert['location']} complete {$done['location']}");

    // 34. After the video's length at 2x has passed, LENGKAP & TERUSKAN saves the lesson and opens the quiz.
    $minWatch = video_min_watch((int)$video['duration']);
    $aged = age_video('self-video', $minWatch - 5);
    $tooSoon = http('POST', 'lesson-complete.php', ['csrf' => $csrfS, 'lesson' => 'self-video']);
    $aged = $aged && age_video('self-video', $minWatch + 5);
    $r = http('POST', 'lesson-complete.php', ['csrf' => $csrfS, 'lesson' => 'self-video']);
    $again = http('GET', 'course.php?lesson=self-video');
    step(34, "video opened {$minWatch}s ago (VIDEO_MIN_WATCH_SECONDS), not before: lesson saved, next is Kuiz Self-Learning; rewatch page has LENGKAP enabled",
        $aged && $tooSoon['status'] === 403 && $r['status'] === 302 && $r['location'] === 'course.php?lesson=kuiz-self-learning'
        && in_array('self-video', progress_keys($idS), true)
        && strpos($again['body'], 'data-done="1"') !== false && preg_match('/id="complete-btn"\s+disabled/', $again['body']) === 0,
        ($aged ? '' : 'could not age the session; ') . "too soon {$tooSoon['status']} complete {$r['status']} {$r['location']}");

    // 35. Kuiz Self-Learning asks every module question; 7/10 fails, 8/10 passes and offers LENGKAP & TERUSKAN.
    $n = count($selfQuiz['questions']);
    $quizPage = http('GET', 'course.php?lesson=kuiz-self-learning');
    $fail = quiz_post('kuiz-self-learning', answers_with($selfQuiz['questions'], 7));
    $failRes = $fail['location'] !== '' ? http('GET', $fail['location']) : ['body' => ''];
    $pass = quiz_post('kuiz-self-learning', answers_with($selfQuiz['questions'], 8));
    $passRes = $pass['location'] !== '' ? http('GET', $pass['location']) : ['body' => ''];
    $m2 = http('GET', 'course.php?lesson=modul-2-pengenalan');
    step(35, "Kuiz Self-Learning has $n questions (no answers in HTML): 7/$n GAGAL (no correct answers shown), 8/$n LULUS with LENGKAP & TERUSKAN and the 2 correct answers",
        $n === 10 && $quizPage['status'] === 200 && !quiz_page_leaks($quizPage['body'], $selfQuiz['questions'])
        && strpos($failRes['body'], 'Markah anda: 7/10 (70%)') !== false && strpos($failRes['body'], 'GAGAL') !== false
        && strpos($passRes['body'], 'Markah anda: 8/10 (80%)') !== false && strpos($passRes['body'], 'action="complete.php"') !== false
        && strpos($passRes['body'], 'name="track" value="self-learning"') !== false
        && strpos($failRes['body'], 'Jawapan betul:') === false
        && substr_count($passRes['body'], 'Jawapan betul:') === 2 && substr_count($passRes['body'], 'review-explain') === 10
        && $m2['status'] === 403 && completion_count($idS) === 0,
        "fail {$fail['location']} pass {$pass['location']} e-learning modul-2 {$m2['status']}");

    // 36. LENGKAP & TERUSKAN gives the shared certificate; E-Learning then counts as finished too.
    $r = http('POST', 'complete.php', ['csrf' => csrf_from($passRes['body']), 'track' => 'self-learning']);
    $cS = completion_find($idS, $courseKey);
    $pdf = http('GET', 'certificate-download.php');
    $pen = http('GET', 'course.php?lesson=penutup');
    $selfPen = http('GET', 'course.php?lesson=self-penutup');
    $choose = http('GET', 'pilih.php');
    $v = $cS !== null ? http('GET', 'verify.php?code=' . rawurlencode($cS['certificate_code'])) : ['body' => ''];
    step(36, 'Self-Learning completion: certificate PDF with the name, verify shows Sijil sah, E-Learning Penutup opens, pilih.php says selesai',
        $r['status'] === 302 && $r['location'] === 'certificate.php' && $cS !== null
        && strncmp($pdf['body'], '%PDF', 4) === 0 && strpos($pdf['body'], 'Ujian ' . $lastS) !== false
        && strpos($v['body'], 'Sijil sah') !== false && $pen['status'] === 200 && $selfPen['status'] === 200
        && strpos($selfPen['body'], 'data-youtube="' . $self->lesson('self-penutup')['youtube'] . '"') !== false
        && strpos($choose['body'], 'Kursus ini telah selesai') !== false,
        "complete {$r['location']} penutup {$pen['status']}");

    // 37. A wrong or missing track never completes the course from the other track's quizzes.
    device('self-2');
    register_learner('Ujian', 'E2ET' . $suffix, 'e2e-t+' . $suffix . '@example.com', false);
    $idT = learner_ids_by_email('e2e-t+' . $suffix . '@example.com')[0] ?? 0;
    $ok = pass_module(1);
    $page = http('GET', 'pilih.php');
    $r1 = http('POST', 'complete.php', ['csrf' => csrf_from($page['body']), 'track' => 'self-learning']);
    $r2 = http('POST', 'complete.php', ['csrf' => csrf_from($page['body']), 'track' => 'bukan-trek']);
    $badLesson = http('POST', 'lesson-complete.php', ['csrf' => csrf_from($page['body']), 'lesson' => 'kuiz-self-learning']);
    step(37, 'complete.php with only Kuiz Modul 1 passed: self-learning goes to its quiz, an unknown track to Kuiz Modul 2; no completion',
        $ok && $r1['location'] === 'course.php?lesson=kuiz-self-learning' && $r2['location'] === 'course.php?lesson=kuiz-modul-2'
        && $badLesson['status'] === 400 && completion_count($idT) === 0,
        "self {$r1['location']} unknown {$r2['location']} bad lesson {$badLesson['status']}");

    // Sambung Semula with the full name instead of the e-mail.
    // 38. S's name in other letter case and with extra spaces resumes S on a fresh device.
    device('name-login');
    $r = resume_with('  ujian   ' . strtolower($lastS) . ' ');
    $choose = http('GET', 'pilih.php');
    step(38, 'Sambung Semula with the full name (any letter case, extra spaces) resumes the learner',
        $r['status'] === 302 && $r['location'] === 'pilih.php' && strpos($choose['body'], 'Selamat kembali, Ujian ' . $lastS) !== false
        && strpos($choose['body'], 'Kursus ini telah selesai') !== false,
        "status {$r['status']} location {$r['location']}");

    // 39. A name nobody registered with: not-found message, nobody signed in.
    device('name-unknown');
    $r = resume_with('Tiada Sesiapa ' . $suffix);
    step(39, 'unknown full name shows the not-found message',
        $r['status'] === 404 && strpos($r['body'], 'Tiada peserta berdaftar dengan nama atau e-mel ini') !== false
        && redirects_to_register('course.php'), "status {$r['status']}");

    // 40. Two different people (different e-mails) with the same name: nobody is resumed by name; the e-mail works.
    $twin = 'Nama Kembar ' . $suffix;
    $twinFields = ['full_name' => $twin, 'age' => 30, 'phone' => '0123456789', 'address' => 'Ujian', 'occupation' => 'Penguji'];
    learner_create(['email' => 'e2e-kembar1+' . $suffix . '@example.com'] + $twinFields, hash('sha256', random_bytes(32)));
    $idTwin2 = learner_create(['email' => 'e2e-kembar2+' . $suffix . '@example.com'] + $twinFields, hash('sha256', random_bytes(32)));
    device('name-twin');
    $r = resume_with($twin);
    $blocked = redirects_to_register('course.php');
    $r2 = resume_with('e2e-kembar2+' . $suffix . '@example.com');
    step(40, 'a name shared by two e-mail addresses asks for the e-mail (409, nobody signed in); the e-mail then resumes',
        $r['status'] === 409 && strpos($r['body'], 'Lebih daripada seorang peserta berdaftar dengan nama ini') !== false && $blocked
        && $r2['status'] === 302 && $r2['location'] === 'pilih.php'
        && strpos(http('GET', 'pilih.php')['body'], 'Hai ' . $twin . '!') !== false && $idTwin2 > 0,
        "name {$r['status']} email {$r2['status']}");

    // 41. Registration validates the single Nama Penuh field.
    device('name-validate');
    $page = http('GET', 'register.php');
    $bad = http('POST', 'register.php', ['csrf' => csrf_from($page['body']), 'full_name' => '   ',
        'email' => 'e2e-v+' . $suffix . '@example.com'] + E2E_REG_FIELDS);
    step(41, 'registration form has one Nama Penuh field and refuses an empty name (422)',
        strpos($page['body'], 'name="full_name"') !== false && strpos($page['body'], 'name="first_name"') === false
        && $bad['status'] === 422 && strpos($bad['body'], 'Sila masukkan nama penuh.') !== false,
        "status {$bad['status']}");

    // 42. sql/migrate-full-name.sql on a scratch copy with the old two name fields: imports twice, fills full_name.
    $scratch = config()['db']['name'] . '_nametest';
    $ok = true;
    [$x] = mysql_run('', "DROP DATABASE IF EXISTS `$scratch`; CREATE DATABASE `$scratch` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $ok = $ok && $x === 0;
    [$x] = mysql_run($scratch, APP_ROOT . '/sql/schema.sql');
    $ok = $ok && $x === 0;
    [$x, , $err] = mysql_run($scratch,
        "ALTER TABLE learners DROP INDEX idx_learners_full_name, DROP INDEX idx_learners_email, DROP COLUMN full_name,
             MODIFY first_name VARCHAR(100) NOT NULL, MODIFY last_name VARCHAR(100) NOT NULL;
         INSERT INTO learners (id, first_name, last_name, age, email, phone, address, occupation, token_hash, created_at)
             VALUES (1, ' Siti ', 'Aminah', 40, 'mig@example.com', '0123456789', 'Ujian', 'Penguji', REPEAT('b', 64), '2026-01-02 03:04:05');");
    $ok = $ok && $x === 0;
    $exits = [];
    for ($i = 0; $i < 2; $i++) {
        [$x, , $e] = mysql_run($scratch, APP_ROOT . '/sql/migrate-full-name.sql');
        $exits[] = $x;
        $err .= $e;
    }
    $c = config()['db'];
    $pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int)($c['port'] ?? 3306), $scratch),
        $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $row = $pdo->query('SELECT full_name, first_name, last_name FROM learners WHERE id = 1')->fetch();
    $idx = $pdo->query("SHOW INDEX FROM learners WHERE Key_name IN ('idx_learners_email', 'idx_learners_full_name')")->fetchAll();
    $pdo->exec("INSERT INTO learners (full_name, age, email, phone, address, occupation, token_hash, created_at)
        VALUES ('Baru Sahaja', 30, 'baru@example.com', '0123456789', 'Ujian', 'Penguji', REPEAT('c', 64), '2026-01-02 03:04:05')");
    $pdo = null;
    [$x] = mysql_run('', "DROP DATABASE IF EXISTS `$scratch`;");
    step(42, "sql/migrate-full-name.sql imports twice on old-format learners ($scratch): full_name filled, names kept, indexes added, new rows insert",
        $ok && $exits === [0, 0] && $row !== false && $row['full_name'] === 'Siti Aminah' && $row['last_name'] === 'Aminah'
        && count($idx) === 2 && $x === 0,
        'exits ' . implode(',', $exits) . ' ' . trim($err));

    // Panel admin. Needs the server started with an admin password hash and the same password in
    // NADI_ADMIN_PASSWORD (php bin/admin-password.php makes the hash).
    $adminPassword = (string)getenv('NADI_ADMIN_PASSWORD');
    $adminIp = hash('sha256', 'admin|127.0.0.1');
    if ($adminPassword === '') {
        echo "SKIP steps 43-47 (panel admin): set NADI_ADMIN_PASSWORD and start the server with its hash\n";
    } else {
        db()->prepare('DELETE FROM resume_attempts WHERE ip_hash = ?')->execute([$adminIp]);
        // 43. Signed out: login form only; a wrong password is refused.
        device('admin');
        $login = http('GET', 'admin.php');
        $wrong = http('POST', 'admin.php', ['csrf' => csrf_from($login['body']), 'action' => 'login', 'password' => 'salah-' . $suffix]);
        $csv = http('GET', 'admin.php?export=csv');
        step(43, 'admin.php signed out: login form, no learner data, wrong password 401, CSV refused',
            $login['status'] === 200 && strpos($login['body'], 'name="password"') !== false && strpos($login['body'], $lastS) === false
            && $wrong['status'] === 401 && strpos($wrong['body'], 'Kata laluan salah.') !== false
            && strpos($csv['body'], 'Nama Penuh,') === false && strpos($csv['type'], 'text/csv') !== 0,
            "login {$login['status']} wrong {$wrong['status']} csv {$csv['type']}");

        // 44. Signed in: figures, quiz table, most-wrong questions and learner S with the Self-Learning certificate.
        $ok = http('POST', 'admin.php', ['csrf' => csrf_from($wrong['body']), 'action' => 'login', 'password' => $adminPassword]);
        $dash = http('GET', 'admin.php');
        $found = http('GET', 'admin.php?' . http_build_query(['q' => strtolower($lastS)]));
        $noCert = http('GET', 'admin.php?' . http_build_query(['q' => $lastS, 'status' => 'belum']));
        $cS = completion_find($idS, $courseKey);
        step(44, 'admin signed in: dashboard with figures, quizzes and most-wrong questions; search finds S with the Self-Learning certificate; filter "belum" hides S',
            $ok['status'] === 302 && $ok['location'] === 'admin.php' && $dash['status'] === 200
            && strpos($dash['body'], 'Peserta berdaftar') !== false && strpos($dash['body'], 'Prestasi Kuiz') !== false
            && strpos($dash['body'], 'Kuiz Self-Learning') !== false && strpos($dash['body'], 'Soalan Paling Kerap Salah') !== false
            && strpos($found['body'], 'Ujian ' . $lastS) !== false && strpos($found['body'], 'Peserta <span class="admin-count">(1)</span>') !== false
            && $cS !== null && strpos($found['body'], $cS['certificate_code']) !== false && strpos($found['body'], '&middot; Self-Learning') !== false
            && strpos($noCert['body'], 'Tiada peserta dijumpai.') !== false,
            "login {$ok['status']} {$ok['location']} dash {$dash['status']}");

        // 45. CSV export of the search: UTF-8 BOM, header, S's row; a formula-like name is neutralised.
        learner_create(['full_name' => '=HYPERLINK("http://x")' . $suffix, 'age' => 30, 'email' => 'e2e-csv+' . $suffix . '@example.com',
            'phone' => '+60123456789', 'address' => 'Ujian', 'occupation' => 'Penguji'], hash('sha256', random_bytes(32)));
        $csvS = http('GET', 'admin.php?' . http_build_query(['q' => $lastS, 'export' => 'csv']));
        $csvF = http('GET', 'admin.php?' . http_build_query(['q' => 'e2e-csv+' . $suffix, 'export' => 'csv']));
        $lines = preg_split('/\r?\n/', trim(substr($csvS['body'], 3)));
        step(45, 'CSV export: text/csv with BOM, header row, one row for S with the certificate; formula-like cells start with an apostrophe',
            strpos($csvS['type'], 'text/csv') === 0 && strncmp($csvS['body'], "\xEF\xBB\xBF", 3) === 0
            && strpos($lines[0] ?? '', 'ID,"Nama Penuh",Umur') === 0 && count($lines) === 2
            && strpos($lines[1], 'Ujian ' . $lastS) !== false && $cS !== null && strpos($lines[1], $cS['certificate_code']) !== false
            && strpos($csvF['body'], '"\'=HYPERLINK(""http://x"")' . $suffix . '"') !== false
            && strpos($lines[0], 'Jantina') !== false && strpos($lines[0], '"Pusat NADI"') !== false && strpos($lines[0], 'Telefon') === false
            && strpos($lines[1], 'PEDi_1045') !== false,
            'type ' . $csvS['type'] . ' lines ' . count($lines) . ' ' . substr($lines[0] ?? '', 0, 40));

        // 46. Log Keluar ends the admin session.
        $out = http('POST', 'admin.php', ['csrf' => csrf_from($dash['body']), 'action' => 'logout']);
        $after = http('GET', 'admin.php');
        step(46, 'Log Keluar signs the admin out',
            $out['status'] === 302 && strpos($after['body'], 'name="password"') !== false && strpos($after['body'], 'Prestasi Kuiz') === false);

        // 47. After 10 wrong passwords from one IP, even the right one is refused for 15 minutes (429).
        $page = $after;
        for ($i = 0; $i < ADMIN_LOGIN_MAX_FAILURES; $i++) {
            $page = http('POST', 'admin.php', ['csrf' => csrf_from($page['body']), 'action' => 'login', 'password' => 'salah']);
        }
        $locked = http('POST', 'admin.php', ['csrf' => csrf_from($page['body']), 'action' => 'login', 'password' => $adminPassword]);
        $stillOut = http('GET', 'admin.php');
        db()->prepare('DELETE FROM resume_attempts WHERE ip_hash = ?')->execute([$adminIp]);
        // Learners on the same IP are not affected by the admin lock.
        device('admin-learner');
        $learnerResume = resume_with($emailS);
        step(47, 'admin sign-in is rate limited (' . ADMIN_LOGIN_MAX_FAILURES . ' failures per IP, then 429 even with the right password); Sambung Semula still works',
            $locked['status'] === 429 && strpos($locked['body'], 'Terlalu banyak cubaan log masuk') !== false
            && strpos($stillOut['body'], 'name="password"') !== false && $learnerResume['location'] === 'pilih.php',
            "locked {$locked['status']} resume {$learnerResume['location']}");
    }

    // Registration fields: Jantina, Pekerjaan, Negeri -> Daerah -> Pusat NADI (no phone or address).
    // 48. The form lists every state, district and centre (works without JavaScript) and has no phone or address.
    device('reg-fields');
    $page = http('GET', 'register.php');
    $tree = nadi_centers();
    $centerCount = array_sum(array_map(static function (array $ds): int {
        return array_sum(array_map('count', $ds));
    }, $tree));
    step(48, "registration form: Jantina, Pekerjaan list, " . count($tree) . " states, every centre ($centerCount) grouped by district, Lain-lain; no phone or address",
        $page['status'] === 200 && strpos($page['body'], 'name="gender" value="Lelaki"') !== false
        && strpos($page['body'], 'name="gender" value="Perempuan"') !== false
        && strpos($page['body'], '<option value="Pesara">Pesara</option>') !== false
        && substr_count($page['body'], '<optgroup label=') === count($tree) + array_sum(array_map('count', $tree))
        && strpos($page['body'], 'value="PIK_0740">NADI Sungai Pinang</option>') !== false
        && substr_count($page['body'], 'value="lain-lain"') === 2
        && strpos($page['body'], 'name="phone"') === false && strpos($page['body'], 'name="address"') === false
        && strpos($page['body'], 'assets/js/register.js') !== false && $centerCount >= 1000,
        "status {$page['status']} centres $centerCount");

    // 49. Wrong combinations are refused with a message on the field; nothing is saved.
    $emailR = 'e2e-reg+' . $suffix . '@example.com';
    $post = static function (array $fields) use ($emailR): array {
        $page = http('GET', 'register.php');
        return http('POST', 'register.php', $fields + ['csrf' => csrf_from($page['body']), 'full_name' => 'Ujian Daftar', 'email' => $emailR] + E2E_REG_FIELDS);
    };
    $cases = [
        'gender'           => [['gender' => 'X'], 'Sila pilih jantina.'],
        'occupation'       => [['occupation' => 'Angkasawan'], 'Sila pilih pekerjaan.'],
        'occupation_other' => [['occupation' => REG_OTHER, 'occupation_other' => ' '], 'Sila nyatakan pekerjaan anda.'],
        'state'            => [['state' => 'Narnia'], 'Sila pilih negeri.'],
        'district'         => [['district' => 'Kuching'], 'Sila pilih daerah.'],
        'nadi'             => [['nadi' => 'PIK_0740'], 'Sila pilih pusat NADI'],
        'nadi_other'       => [['nadi' => REG_OTHER, 'nadi_other' => ''], 'Sila tulis nama pusat NADI anda.'],
    ];
    $bad = [];
    foreach ($cases as $field => [$override, $msg]) {
        $r = $post($override);
        if ($r['status'] !== 422 || strpos($r['body'], $msg) === false || strpos($r['body'], 'id="e-' . $field . '"') === false) {
            $bad[] = $field . ':' . $r['status'];
        }
    }
    step(49, 'registration refuses a bad gender, occupation, state, a district of another state, a centre of another district and empty Lain-lain fields (422)',
        $bad === [] && learner_ids_by_email($emailR) === [], implode(' ', $bad));

    // 50. Lain-lain: the typed occupation and centre are saved (no centre code).
    $r = $post(['occupation' => REG_OTHER, 'occupation_other' => '  Tukang   Jahit ', 'nadi' => REG_OTHER, 'nadi_other' => 'NADI Kampung Ujian ' . $suffix]);
    $row = db()->prepare('SELECT * FROM learners WHERE email = ?');
    $row->execute([$emailR]);
    $row = $row->fetch() ?: [];
    step(50, 'Lain-lain occupation and centre are saved as typed (spaces collapsed), with the chosen state and district and no centre code',
        $r['status'] === 302 && $r['location'] === 'pilih.php' && ($row['occupation'] ?? '') === 'Tukang Jahit'
        && ($row['nadi_name'] ?? '') === 'NADI Kampung Ujian ' . $suffix && ($row['nadi_code'] ?? 'x') === ''
        && ($row['state'] ?? '') === 'Johor' && ($row['district'] ?? '') === 'Batu Pahat',
        "status {$r['status']} " . json_encode(array_intersect_key($row, array_flip(['occupation', 'nadi_name', 'nadi_code']))));

    // 51. sql/migrate-registration-fields.sql on a scratch copy without the new columns: imports twice, keeps old data.
    $scratch = config()['db']['name'] . '_regtest';
    [$x1] = mysql_run('', "DROP DATABASE IF EXISTS `$scratch`; CREATE DATABASE `$scratch` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    [$x2] = mysql_run($scratch, APP_ROOT . '/sql/schema.sql');
    [$x3, , $err] = mysql_run($scratch,
        "ALTER TABLE learners DROP INDEX idx_learners_nadi, DROP COLUMN gender, DROP COLUMN state, DROP COLUMN district,
             DROP COLUMN nadi_code, DROP COLUMN nadi_name, MODIFY phone VARCHAR(20) NOT NULL, MODIFY address VARCHAR(255) NOT NULL;
         INSERT INTO learners (id, full_name, age, email, phone, address, occupation, token_hash, created_at)
             VALUES (1, 'Siti Lama', 40, 'lama@example.com', '0123456789', 'Kuching', 'Penguji', REPEAT('d', 64), '2026-01-02 03:04:05');");
    $exits = [];
    for ($i = 0; $i < 2; $i++) {
        [$x, , $e] = mysql_run($scratch, APP_ROOT . '/sql/migrate-registration-fields.sql');
        $exits[] = $x;
        $err .= $e;
    }
    $c = config()['db'];
    $pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int)($c['port'] ?? 3306), $scratch),
        $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $old = $pdo->query('SELECT phone, address, gender, state, nadi_name FROM learners WHERE id = 1')->fetch();
    $idx = $pdo->query("SHOW INDEX FROM learners WHERE Key_name = 'idx_learners_nadi'")->fetchAll();
    $pdo->exec("INSERT INTO learners (full_name, age, gender, email, occupation, state, district, nadi_code, nadi_name, token_hash, created_at)
        VALUES ('Baru', 30, 'Lelaki', 'baru@example.com', 'Pesara', 'Johor', 'Batu Pahat', 'PEDi_1045', 'NADI X', REPEAT('e', 64), '2026-01-02 03:04:05')");
    $pdo = null;
    [$x4] = mysql_run('', "DROP DATABASE IF EXISTS `$scratch`;");
    step(51, "sql/migrate-registration-fields.sql imports twice on the old format ($scratch): columns and index added, old phone and address kept, new rows need no phone",
        [$x1, $x2, $x3, $x4] === [0, 0, 0, 0] && $exits === [0, 0] && $old !== false && $old['phone'] === '0123456789'
        && $old['address'] === 'Kuching' && $old['gender'] === '' && $old['nadi_name'] === '' && count($idx) === 3,
        'exits ' . implode(',', $exits) . ' ' . trim($err));
} catch (Throwable $ex) {
    $failures++;
    echo 'FAIL aborted: ' . $ex->getMessage() . "\n";
}

foreach ($devices as $h) {
    curl_close($h);
}
foreach ($jars as $jar) {
    @unlink($jar);
}

echo $failures === 0 ? "\nAll e2e steps passed.\n" : "\n$failures e2e step(s) failed.\n";
exit($failures === 0 ? 0 : 1);
