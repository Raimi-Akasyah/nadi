<?php
declare(strict_types=1);

// End-to-end test against a running server (needs the DB from config/config.php).
// Run: & 'C:\xampp 8.2\php\php.exe' tests/e2e.php http://127.0.0.1:8080 [path-to-save-certificate.pdf]

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

/** Register a learner in the test browser; returns the redirect response. */
function register_learner(string $first, string $last, string $email, bool $remember): array
{
    $page = http('GET', 'register.php');
    $fields = [
        'csrf' => csrf_from($page['body']), 'first_name' => $first, 'last_name' => $last, 'age' => '30',
        'email' => $email, 'phone' => '+60 12-345 6789', 'address' => 'No 1, Jalan Ujian, Kuala Lumpur', 'occupation' => 'Penguji',
    ];
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

/** "Sambung Semula" with an e-mail on the current device; returns the response. */
function resume_with(string $email, bool $remember = false): array
{
    $page = http('GET', 'resume.php');
    $fields = ['csrf' => csrf_from($page['body']), 'email' => $email];
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
$quiz = $course->quiz();
$courseKey = $course->key();
$quizKey = $course->quizLesson()['key'];

/** Answers with the first $correct questions right and the rest wrong. */
function answers_with(array $quiz, int $correct): array
{
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
}

function quiz_post(array $answers): array
{
    $page = http('GET', 'course.php?lesson=kuiz-akhir');
    $csrf = csrf_from($page['body']);
    if ($csrf === '') {
        // The gate page has no form; the token is per session, so take it from a lesson page.
        $csrf = csrf_from(http('GET', 'course.php?lesson=modul-1-pengenalan')['body']);
    }
    $fields = ['csrf' => $csrf];
    foreach ($answers as $qid => $opt) {
        $fields["answers[$qid]"] = $opt;
    }
    return http('POST', 'quiz-submit.php', $fields);
}

function latest_attempt(int $learnerId): ?array
{
    $stmt = db()->prepare('SELECT * FROM quiz_attempts WHERE learner_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$learnerId]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

try {
    // 1. Landing page.
    $r = http('GET', '/');
    step(1, 'GET / returns 200 with Daftar Sekarang', $r['status'] === 200 && strpos($r['body'], 'Daftar Sekarang') !== false, "status {$r['status']}");

    // 2. Registration.
    $suffix = (string)time();
    $first = 'Ujian';
    $last = 'E2E' . $suffix;
    $email = 'e2e+' . $suffix . '@example.com';
    $page = http('GET', 'register.php');
    $r = http('POST', 'register.php', [
        'csrf' => csrf_from($page['body']), 'first_name' => $first, 'last_name' => $last, 'age' => '30',
        'email' => $email, 'phone' => '+60 12-345 6789', 'address' => 'No 1, Jalan Ujian, Kuala Lumpur', 'occupation' => 'Penguji',
    ]);
    $stmt = db()->prepare('SELECT * FROM learners WHERE email = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$email]);
    $learner = $stmt->fetch() ?: null;
    step(2, 'registration redirects to course.php and saves the learner',
        $r['status'] === 302 && $r['location'] === 'course.php' && $learner !== null
        && $learner['first_name'] === $first && $learner['last_name'] === $last,
        "status {$r['status']} location {$r['location']}");
    if ($learner === null) {
        throw new RuntimeException('no learner row, cannot continue');
    }
    $learnerId = (int)$learner['id'];

    // 3. Quiz gated before Modul 1-5 are completed.
    $page = http('GET', 'course.php?lesson=kuiz-akhir');
    $gateShown = strpos($page['body'], 'Sila lengkapkan semua modul') !== false
        && strpos($page['body'], 'quiz-submit.php') === false;
    $r = quiz_post(answers_with($quiz, 10));
    step(3, 'quiz gated before modules complete (gate message, submit 403)', $gateShown && $r['status'] === 403, "submit status {$r['status']}");

    // 4. Viewer navigation and lesson completion.
    $m1 = count($course->lesson('modul-1-pengenalan')['slides']);
    $s1 = http('GET', 'course.php?lesson=modul-1-pengenalan&slide=1');
    $sN = http('GET', 'course.php?lesson=modul-1-pengenalan&slide=' . $m1);
    $ok = strpos($s1['body'], ">1/$m1<") !== false && strpos($sN['body'], ">$m1/$m1<") !== false;
    for ($n = 1; $n <= 5; $n++) {
        $key = "modul-$n-pengenalan";
        $slides = count($course->lesson($key)['slides']);
        $page = http('GET', 'course.php?lesson=' . $key . '&slide=' . $slides);
        $r = http('POST', 'lesson-complete.php', ['csrf' => csrf_from($page['body']), 'lesson' => $key]);
        $next = $course->nextLesson($key)['key'];
        if ($r['status'] !== 302 || $r['location'] !== 'course.php?lesson=' . $next) {
            $ok = false;
        }
    }
    step(4, "viewer 1/$m1 -> $m1/$m1 and lesson-complete on each module's last slide leads to the next lesson for Modul 1-5", $ok);

    // 5. Completion refused before passing.
    $page = http('GET', 'course.php?lesson=kuiz-akhir');
    $r = http('POST', 'complete.php', ['csrf' => csrf_from($page['body'])]);
    $stmt = db()->prepare('SELECT COUNT(*) FROM completions WHERE learner_id = ?');
    $stmt->execute([$learnerId]);
    step(5, 'complete.php before passing creates no completion', (int)$stmt->fetchColumn() === 0 && $r['status'] === 302,
        "status {$r['status']}");

    // Correct answers are never in the quiz page HTML.
    $leak = false;
    foreach ($quiz['questions'] as $q) {
        if (strpos($page['body'], 'name="answers[' . $q['id'] . ']"') === false) {
            $leak = true;
        }
    }
    $leak = $leak || strpos($page['body'], 'answer"') !== false || strpos($page['body'], 'checked') !== false;

    // 6. Failing attempt (7/10).
    $r = quiz_post(answers_with($quiz, 7));
    $a = latest_attempt($learnerId);
    $res = $r['location'] !== '' ? http('GET', $r['location']) : ['body' => ''];
    step(6, '7/10 attempt stored passed=0 and shows GAGAL + Cuba Lagi (quiz form has no answers)',
        !$leak && $a !== null && (int)$a['correct_count'] === 7 && (int)$a['passed'] === 0
        && strpos($res['body'], 'Markah anda: 7/10 (70%)') !== false
        && strpos($res['body'], 'GAGAL') !== false && strpos($res['body'], 'Cuba Lagi') !== false,
        'leak=' . ($leak ? 'yes' : 'no') . " location {$r['location']}");

    // 7. Passing attempt (8/10).
    $r = quiz_post(answers_with($quiz, 8));
    $a = latest_attempt($learnerId);
    $res = $r['location'] !== '' ? http('GET', $r['location']) : ['body' => ''];
    step(7, '8/10 attempt stored passed=1 and shows LULUS',
        $a !== null && (int)$a['correct_count'] === 8 && (int)$a['passed'] === 1
        && strpos($res['body'], 'Markah anda: 8/10 (80%)') !== false && strpos($res['body'], 'LULUS') !== false
        && strpos($res['body'], 'complete.php') !== false);

    // 8. Completion.
    $r = http('POST', 'complete.php', ['csrf' => csrf_from($res['body'])]);
    $completion = completion_find($learnerId, $courseKey);
    $code = $completion['certificate_code'] ?? '';
    step(8, 'complete.php creates a completion with a NADI-XXXXXXXXXX code and redirects to certificate.php',
        $completion !== null && preg_match('/^NADI-[0-9A-F]{10}$/', $code) === 1
        && $r['status'] === 302 && $r['location'] === 'certificate.php',
        "status {$r['status']} location {$r['location']} code $code");

    // 9. Certificate PDF.
    $cert = http('GET', 'certificate.php');
    $pdf = http('GET', 'certificate-download.php');
    $today = format_date_ms(date('Y-m-d H:i:s'));
    step(9, 'certificate PDF has the name, today\'s date and the code',
        strpos($cert['body'], 'Tahniah!') !== false
        && strpos($pdf['type'], 'application/pdf') === 0 && strncmp($pdf['body'], '%PDF', 4) === 0
        && strpos($pdf['body'], "$first $last") !== false && strpos($pdf['body'], $today) !== false
        && $code !== '' && strpos($pdf['body'], $code) !== false,
        "type {$pdf['type']} date $today");
    if (isset($argv[2]) && $argv[2] !== '') {
        file_put_contents($argv[2], $pdf['body']);
    }

    // 10. Public verification.
    $v = http('GET', 'verify.php?code=' . rawurlencode(strtolower($code)));
    step(10, 'verify.php shows Sijil sah with the name',
        strpos($v['body'], 'Sijil sah') !== false && strpos($v['body'], "$first $last") !== false);

    // Shared computer: learner A (above) registered without "Ingat saya", finished and now leaves.
    // 11. No persistent learner cookie, and the certificate page offers "Selesai / Keluar".
    $jarNow = cookies();
    step(11, 'A has no persistent learner cookie and the certificate page shows Selesai / Keluar',
        !isset($jarNow[LEARNER_COOKIE]) && strpos($cert['body'], 'Selesai / Keluar') !== false
        && strpos($cert['body'], 'action="reset.php"') !== false,
        'cookies: ' . implode(',', array_keys($jarNow)));

    // 12. A presses "Selesai / Keluar".
    $r = http('POST', 'reset.php', ['csrf' => csrf_from($cert['body'])]);
    step(12, 'Selesai / Keluar signs A out and redirects to index.php?keluar=1',
        $r['status'] === 302 && $r['location'] === 'index.php?keluar=1', "status {$r['status']} location {$r['location']}");

    // 13. The next person on the same browser sees nothing of A.
    $bye = http('GET', $r['location']);
    $home = http('GET', '/');
    $reg = http('GET', 'register.php');
    step(13, 'next visitor sees no name or progress of A and cannot open or download A\'s certificate',
        strpos($bye['body'], 'Anda telah keluar') !== false
        && strpos($home['body'], 'Daftar Sekarang') !== false
        && strpos($home['body'] . $bye['body'] . $reg['body'], $last) === false
        && strpos($reg['body'], $email) === false
        && redirects_to_register('course.php') && redirects_to_register('course.php?lesson=kuiz-akhir')
        && redirects_to_register('certificate.php') && redirects_to_register('certificate-download.php'));

    // 14. Shared computer, learner leaves without pressing Keluar and the browser is closed.
    $lastB = 'E2EB' . $suffix;
    register_learner('Ujian', $lastB, 'e2e-b+' . $suffix . '@example.com', false);
    $before = http('GET', '/');
    close_browser();
    $after = http('GET', '/');
    step(14, 'without Keluar, closing the browser forgets a learner who did not tick Ingat saya',
        strpos($before['body'], $lastB) !== false && strpos($after['body'], $lastB) === false
        && strpos($after['body'], 'Daftar Sekarang') !== false && redirects_to_register('certificate-download.php'));

    // 15. Shared computer, learner walks away and the browser stays open: idle timeout.
    $lastC = 'E2EC' . $suffix;
    register_learner('Ujian', $lastC, 'e2e-c+' . $suffix . '@example.com', false);
    $before = http('GET', '/');
    $aged = age_session(LEARNER_IDLE_MINUTES * 60 + 60);
    $after = http('GET', '/');
    step(15, 'after ' . LEARNER_IDLE_MINUTES . ' idle minutes the learner is signed out',
        $aged && strpos($before['body'], $lastC) !== false && strpos($after['body'], $lastC) === false
        && redirects_to_register('course.php'),
        $aged ? '' : 'could not age the session file');

    // 16. Own device: "Ingat saya" keeps the learner across a browser restart until "Bukan anda? Mula semula".
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
    step(16, 'Ingat saya keeps the learner for ' . LEARNER_COOKIE_DAYS . ' days; Mula semula removes the cookie',
        $remembered && $r['status'] === 302 && !isset(cookies()[LEARNER_COOKIE])
        && strpos($home['body'], $lastD) === false && strpos($home['body'], 'Daftar Sekarang') !== false);

    // 17. A remembered learner's cookie does not survive the next person registering without "Ingat saya".
    $lastE = 'E2EE' . $suffix;
    $lastF = 'E2EF' . $suffix;
    register_learner('Ujian', $lastE, 'e2e-e+' . $suffix . '@example.com', true);
    register_learner('Ujian', $lastF, 'e2e-f+' . $suffix . '@example.com', false);
    $c = cookies()[LEARNER_COOKIE] ?? null;
    $hadCookie = $c !== null && $c['expires'] > time(); // curl may keep a deleted cookie as already expired
    close_browser();
    $home = http('GET', '/');
    step(17, 'registering without Ingat saya clears an earlier learner\'s remember cookie',
        !$hadCookie && strpos($home['body'], $lastE) === false && strpos($home['body'], $lastF) === false,
        'cookie=' . ($hadCookie ? 'yes' : 'no') . ' E=' . (strpos($home['body'], $lastE) !== false ? 'shown' : 'hidden')
        . ' F=' . (strpos($home['body'], $lastF) !== false ? 'shown' : 'hidden'));

    // "Sambung Semula" (resume by e-mail). Rate-limit rows from earlier local runs would get in the way.
    $localIp = hash('sha256', '127.0.0.1');
    db()->prepare('DELETE FROM resume_attempts WHERE ip_hash = ?')->execute([$localIp]);
    $allLessons = count($course->lessons());

    // 18. Device 1: learner G registers without "Ingat saya", completes Modul 1-2, presses Keluar.
    device('device-1');
    $lastG = 'E2EG' . $suffix;
    $emailG = 'e2e-g+' . $suffix . '@example.com';
    register_learner('Ujian', $lastG, $emailG, false);
    $ok = complete_lessons(['modul-1-pengenalan', 'modul-2-pengenalan']);
    $page = http('GET', 'course.php');
    $r = http('POST', 'reset.php', ['csrf' => csrf_from($page['body'])]);
    $idsG = learner_ids_by_email($emailG);
    step(18, 'device 1: G completes 2 lessons, the course page has Selesai / Keluar, G signs out',
        $ok && count($idsG) === 1 && count(progress_keys($idsG[0])) === 2
        && strpos($page['body'], 'Selesai / Keluar') !== false && $r['location'] === 'index.php?keluar=1');

    // 19. Device 2 (fresh browser): Sambung Semula with G's e-mail (different letter case) restores G's progress.
    device('device-2');
    $home = http('GET', '/');
    $r = resume_with(strtoupper($emailG));
    $page = http('GET', 'course.php');
    $pct = (int)round(2 * 100 / $allLessons);
    step(19, 'device 2: Sambung Semula with G\'s e-mail restores G\'s progress (no remember cookie)',
        strpos($home['body'], 'Sambung Semula') !== false && strpos($home['body'], 'action="resume.php"') !== false
        && $r['status'] === 302 && $r['location'] === 'course.php'
        && strpos($page['body'], 'Kemajuan anda telah disambung semula') !== false
        && strpos($page['body'], $lastG) !== false
        && strpos($page['body'], 'progress-text">' . $pct . '% selesai') !== false
        && !isset(cookies()[LEARNER_COOKIE]),
        "status {$r['status']} location {$r['location']}");

    // 20. G finishes on device 2 and downloads the certificate with G's name.
    $ok = complete_lessons(['modul-3-pengenalan', 'modul-4-pengenalan', 'modul-5-pengenalan']);
    $r = quiz_post(answers_with($quiz, 9));
    $res = http('GET', $r['location']);
    $r = http('POST', 'complete.php', ['csrf' => csrf_from($res['body'])]);
    $pdf = http('GET', 'certificate-download.php');
    step(20, 'device 2: G finishes, passes and downloads a certificate with G\'s name',
        $ok && $r['location'] === 'certificate.php' && strncmp($pdf['body'], '%PDF', 4) === 0
        && strpos($pdf['body'], 'Ujian ' . $lastG) !== false && completion_find($idsG[0], $courseKey) !== null);

    // 21. Unknown e-mail: Malay not-found message with a link to registration.
    device('device-3');
    $r = resume_with('tiada-' . $suffix . '@example.com');
    step(21, 'unknown e-mail shows the Malay not-found message and offers registration',
        $r['status'] === 404 && strpos($r['body'], 'Tiada peserta berdaftar dengan e-mel ini') !== false
        && strpos($r['body'], 'href="register.php"') !== false && redirects_to_register('course.php'),
        "status {$r['status']}");

    // 22. Registering again with G's e-mail resumes G (no duplicate row; G's name stays on the certificate).
    $r = register_learner('Lain', 'Orang' . $suffix, $emailG, false);
    $page = http('GET', 'course.php');
    $cert = http('GET', 'certificate.php');
    step(22, 're-registering with an existing e-mail resumes that learner without creating a duplicate',
        $r['status'] === 302 && $r['location'] === 'course.php' && learner_ids_by_email($emailG) === $idsG
        && strpos($page['body'], 'E-mel ini sudah berdaftar') !== false
        && strpos($cert['body'], 'Ujian ' . $lastG) !== false && strpos($cert['body'], 'Orang' . $suffix) === false);

    // 23. Duplicate e-mails left by earlier testing: the record with the most progress is resumed.
    $emailDup = 'e2e-dup+' . $suffix . '@example.com';
    $dupFields = ['age' => 30, 'email' => $emailDup, 'phone' => '0123456789', 'address' => 'Ujian', 'occupation' => 'Penguji'];
    $older = learner_create(['first_name' => 'Lama', 'last_name' => 'Dup' . $suffix] + $dupFields, hash('sha256', random_bytes(32)));
    $newer = learner_create(['first_name' => 'Baru', 'last_name' => 'Dup' . $suffix] + $dupFields, hash('sha256', random_bytes(32)));
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
    step(23, 'duplicate e-mail: the learner with the most progress is resumed, the newest on a tie; no rows removed',
        $pickMost && $tieNewest && learner_ids_by_email($emailDup) === [$older, $newer]);

    // 24. Long reading: more than the idle timeout inside one module, with slide activity, keeps the learner.
    device('reader');
    $lastH = 'E2EH' . $suffix;
    register_learner('Ujian', $lastH, 'e2e-h+' . $suffix . '@example.com', false);
    $page = http('GET', 'course.php?lesson=modul-4-pengenalan&slide=1');
    $csrf = csrf_from($page['body']);
    $ok = strpos($page['body'], 'assets/js/course.js') !== false;
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
    step(24, '80 minutes reading Modul 4 with keep-alive pings: lesson-complete still saves the progress',
        $ok && $r['status'] === 302 && $r['location'] === 'course.php?lesson=modul-5-pengenalan'
        && in_array('modul-4-pengenalan', progress_keys($idH), true) && $anon['status'] === 401,
        "complete status {$r['status']} location {$r['location']} anon keepalive {$anon['status']}");

    // 25. "Ingat saya" on two own devices: resuming with the box ticked on device B keeps device A remembered.
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
    step(25, 'Sambung Semula with Ingat saya sets a 30-day cookie and the first device stays remembered',
        $laptopCookie !== null && $laptopCookie['expires'] > time() + (LEARNER_COOKIE_DAYS - 1) * 86400
        && strpos($laptop['body'], $lastI) !== false && strpos($phone['body'], $lastI) !== false);

    // 26. Rate limit: after RESUME_MAX_PER_EMAIL tries for one e-mail, the next one is refused with 429.
    device('attacker');
    $target = 'e2e-rl+' . $suffix . '@example.com';
    $statuses = [];
    for ($i = 0; $i <= RESUME_MAX_PER_EMAIL; $i++) {
        $statuses[] = resume_with($target)['status'];
    }
    $last = array_pop($statuses);
    $r = resume_with($target);
    step(26, 'Sambung Semula is rate limited (' . RESUME_MAX_PER_EMAIL . ' tries per e-mail per '
        . RESUME_WINDOW_MINUTES . ' minutes)',
        array_unique($statuses) === [404] && $last === 429 && strpos($r['body'], 'Terlalu banyak cubaan') !== false,
        'statuses ' . implode(',', $statuses) . " then $last");
    db()->prepare('DELETE FROM resume_attempts WHERE ip_hash = ?')->execute([$localIp]);
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
