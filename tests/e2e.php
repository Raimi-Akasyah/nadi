<?php
declare(strict_types=1);

// End-to-end test against a running server (needs the DB from config/config.php).
// Run: & 'C:\xampp 8.2\php\php.exe' tests/e2e.php http://127.0.0.1:8080

require __DIR__ . '/../src/bootstrap.php';

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$jar = tempnam(sys_get_temp_dir(), 'nadi_e2e_');
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEJAR      => $jar,
    CURLOPT_COOKIEFILE     => $jar,
    CURLOPT_TIMEOUT        => 20,
]);

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
    $s1 = http('GET', 'course.php?lesson=modul-1-pengenalan&slide=1');
    $s5 = http('GET', 'course.php?lesson=modul-1-pengenalan&slide=5');
    $ok = strpos($s1['body'], '>1/5<') !== false && strpos($s5['body'], '>5/5<') !== false;
    for ($n = 1; $n <= 5; $n++) {
        $key = "modul-$n-pengenalan";
        $page = http('GET', 'course.php?lesson=' . $key . '&slide=5');
        $r = http('POST', 'lesson-complete.php', ['csrf' => csrf_from($page['body']), 'lesson' => $key]);
        $next = $course->nextLesson($key)['key'];
        if ($r['status'] !== 302 || $r['location'] !== 'course.php?lesson=' . $next) {
            $ok = false;
        }
    }
    step(4, 'viewer 1/5 -> 5/5 and lesson-complete leads to the next lesson for Modul 1-5', $ok);

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

    // 10. Public verification.
    $v = http('GET', 'verify.php?code=' . rawurlencode(strtolower($code)));
    step(10, 'verify.php shows Sijil sah with the name',
        strpos($v['body'], 'Sijil sah') !== false && strpos($v['body'], "$first $last") !== false);
} catch (Throwable $ex) {
    $failures++;
    echo 'FAIL aborted: ' . $ex->getMessage() . "\n";
}

curl_close($ch);
@unlink($jar);

echo $failures === 0 ? "\nAll e2e steps passed.\n" : "\n$failures e2e step(s) failed.\n";
exit($failures === 0 ? 0 : 1);
