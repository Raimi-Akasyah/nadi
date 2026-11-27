<?php
declare(strict_types=1);

/**
 * Admin panel (public/admin.php): sign-in, dashboard figures, the learner list and the CSV export.
 * The panel is on only when config 'admin' => ['password_hash' => ...] (or ADMIN_PASSWORD_HASH) holds a hash made
 * with bin/admin-password.php. SQL is in src/repositories.php.
 */

const ADMIN_IDLE_MINUTES = 30;
// Failed sign-ins per IP address within the window before further tries are refused. They are counted in
// resume_attempts under an IP hash of their own, so they never use up a learner's Sambung Semula quota.
const ADMIN_LOGIN_WINDOW_MINUTES = 15;
const ADMIN_LOGIN_MAX_FAILURES = 10;
const ADMIN_PAGE_SIZE = 50;

function admin_password_hash(): string
{
    $hash = config()['admin']['password_hash'] ?? '';
    return is_string($hash) ? trim($hash) : '';
}

/** True when a valid password hash is configured. */
function admin_enabled(): bool
{
    $hash = admin_password_hash();
    return $hash !== '' && !empty(password_get_info($hash)['algo']);
}

/** True while the admin is signed in in this session; each request restarts the idle timeout. */
function admin_signed_in(): bool
{
    $at = $_SESSION['admin_active'] ?? null;
    if (!is_int($at)) {
        return false;
    }
    if (time() - $at > ADMIN_IDLE_MINUTES * 60) {
        unset($_SESSION['admin_active']);
        return false;
    }
    $_SESSION['admin_active'] = time();
    return true;
}

function admin_ip_hash(): string
{
    return hash('sha256', 'admin|' . (string)($_SERVER['REMOTE_ADDR'] ?? ''));
}

/** @return string 'ok', 'wrong' or 'locked' (too many failed tries from this IP address) */
function admin_sign_in(string $password): string
{
    $ip = admin_ip_hash();
    $tag = hash('sha256', 'admin-login');
    $since = date('Y-m-d H:i:s', time() - ADMIN_LOGIN_WINDOW_MINUTES * 60);
    if (resume_attempt_counts($ip, $tag, $since)['ip'] >= ADMIN_LOGIN_MAX_FAILURES) {
        return 'locked';
    }
    if (!password_verify($password, admin_password_hash())) {
        resume_attempt_record($ip, $tag);
        return 'wrong';
    }
    session_regenerate_id(true);
    $_SESSION['admin_active'] = time();
    return 'ok';
}

function admin_sign_out(): void
{
    unset($_SESSION['admin_active']);
    session_regenerate_id(true);
}

/** The track a completion came from, by the quiz key of its attempt (the old Kuiz Akhir counts as E-Learning). */
function admin_certificate_track(?string $quizKey): Course
{
    $course = is_string($quizKey) && $quizKey !== '' ? Course::forLesson($quizKey) : null;
    return $course ?? Course::load();
}

/**
 * Learner rows from admin_learners_find() with their progress.
 * @return list<array{learner: array, name: string, percent: array<string, int>, quizzes_passed: int, attempts: int,
 *                    certificate: ?array{code: string, date: string, track: string}}>
 */
function admin_learner_summaries(array $rows): array
{
    $tracks = Course::all();
    $quizKeys = [];
    foreach ($tracks as $t) {
        foreach ($t->quizLessons() as $q) {
            $quizKeys[] = $q['key'];
        }
    }
    $progress = admin_progress_for(array_column($rows, 'id'), $tracks[0]->key());
    $out = [];
    foreach ($rows as $row) {
        $p = $progress[(int)$row['id']] ?? ['lessons' => [], 'passed' => [], 'attempts' => 0];
        $completed = !empty($row['certificate_code']);
        $done = array_fill_keys($p['lessons'], true);
        $percent = [];
        foreach ($tracks as $t) {
            $percent[$t->track()] = $t->percentDone($done + $t->effectivePassed($p['passed'], $completed));
        }
        $out[] = [
            'learner'        => $row,
            'name'           => learner_name($row),
            'percent'        => $percent,
            'quizzes_passed' => count(array_intersect($quizKeys, $p['passed'])),
            'attempts'       => (int)$p['attempts'],
            'certificate'    => $completed ? [
                'code'  => (string)$row['certificate_code'],
                'date'  => (string)$row['certificate_at'],
                'track' => admin_certificate_track($row['certificate_quiz'] ?? null)->trackTitle(),
            ] : null,
        ];
    }
    return $out;
}

/**
 * Questions answered wrong most often (an unanswered question counts as wrong). Pure apart from the course content.
 * @param array $attemptRows rows with quiz_key and answers_json
 * @return list<array{id: string, text: string, quizzes: string, asked: int, wrong: int, percent: int}>
 */
function admin_question_stats(array $attemptRows, int $top = 5): array
{
    $byQuiz = [];
    $titles = [];
    foreach (Course::all() as $t) {
        foreach ($t->quizLessons() as $ql) {
            $byQuiz[$ql['key']] = $t->quiz($ql['key'])['questions'];
            foreach ($byQuiz[$ql['key']] as $q) {
                $titles[$q['id']][$ql['title']] = true;
            }
        }
    }
    $stats = [];
    foreach ($attemptRows as $row) {
        $questions = $byQuiz[(string)$row['quiz_key']] ?? null;
        $answers = json_decode((string)$row['answers_json'], true);
        if ($questions === null || !is_array($answers)) {
            continue;
        }
        foreach ($questions as $q) {
            $s = $stats[$q['id']] ?? ['id' => (string)$q['id'], 'text' => (string)$q['text'], 'asked' => 0, 'wrong' => 0];
            $s['asked']++;
            if (($answers[$q['id']] ?? null) !== $q['answer']) {
                $s['wrong']++;
            }
            $stats[$q['id']] = $s;
        }
    }
    $stats = array_values(array_filter($stats, static function (array $s): bool {
        return $s['wrong'] > 0;
    }));
    foreach ($stats as &$s) {
        $s['percent'] = (int)round($s['wrong'] * 100 / $s['asked']);
        $s['quizzes'] = implode(', ', array_keys($titles[$s['id']] ?? []));
    }
    unset($s);
    usort($stats, static function (array $a, array $b): int {
        return [$b['percent'], $b['asked']] <=> [$a['percent'], $a['asked']];
    });
    return array_slice($stats, 0, $top);
}

/** Everything the dashboard shows apart from the learner list. */
function admin_dashboard(): array
{
    $tracks = Course::all();
    $courseKey = $tracks[0]->key();
    $lessonKeys = [];
    foreach ($tracks as $t) {
        $lessonKeys[$t->track()] = array_column($t->lessons(), 'key');
    }
    $counts = admin_counts($courseKey, date('Y-m-d H:i:s', time() - 7 * 86400), $lessonKeys);
    $certByTrack = array_fill_keys(array_keys(Course::TRACKS), 0);
    foreach ($counts['certificates'] as $quizKey => $n) {
        $certByTrack[admin_certificate_track($quizKey)->track()] += $n;
    }
    $quizCounts = admin_quiz_counts($courseKey);
    $quizzes = [];
    foreach ($tracks as $t) {
        foreach ($t->quizLessons() as $q) {
            $c = $quizCounts[$q['key']] ?? [];
            $quizzes[] = [
                'track'           => $t->trackTitle(),
                'title'           => $q['title'],
                'learners'        => $c['learners'] ?? 0,
                'passed_learners' => $c['passed_learners'] ?? 0,
                'attempts'        => $c['attempts'] ?? 0,
                'passes'          => $c['passes'] ?? 0,
            ];
        }
    }
    return [
        'tracks'       => $tracks,
        'learners'     => $counts['learners'],
        'learners_new' => $counts['learners_new'],
        'started'      => $counts['started'],
        'certificates' => array_sum($certByTrack),
        'cert_tracks'  => $certByTrack,
        'quizzes'      => $quizzes,
        'questions'    => admin_question_stats(admin_recent_answers($courseKey, 5000)),
    ];
}

/** A CSV cell that a spreadsheet will not run as a formula (OWASP CSV injection: prefix with an apostrophe). */
function admin_csv_cell($value): string
{
    $s = (string)$value;
    return preg_match('/^[=+\-@\t\r]/', $s) === 1 ? "'" . $s : $s;
}

/** Stream the learners matching the current search and filter as CSV (UTF-8 with BOM, so Excel shows Malay text). */
function admin_export_csv(string $search, string $status): void
{
    $tracks = Course::all();
    $courseKey = $tracks[0]->key();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="peserta-nadi-' . date('Ymd-His') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $head = ['ID', 'Nama Penuh', 'Umur', 'Jantina', 'E-mel', 'Pekerjaan', 'Negeri', 'Daerah', 'Kod NADI', 'Pusat NADI', 'Tarikh Daftar'];
    foreach ($tracks as $t) {
        $head[] = $t->trackTitle() . ' (%)';
    }
    array_push($head, 'Kuiz Lulus', 'Jumlah Percubaan Kuiz', 'No. Sijil', 'Tarikh Sijil', 'Sijil Melalui');
    fputcsv($out, $head, ',', '"', '');
    $batch = 500;
    for ($offset = 0; ; $offset += $batch) {
        $rows = admin_learners_find($courseKey, $search, $status, $batch, $offset);
        foreach (admin_learner_summaries($rows) as $s) {
            $l = $s['learner'];
            $line = [$l['id'], $s['name'], $l['age'], $l['gender'] ?? '', $l['email'], $l['occupation'], $l['state'] ?? '',
                $l['district'] ?? '', $l['nadi_code'] ?? '', $l['nadi_name'] ?? '', $l['created_at']];
            foreach ($tracks as $t) {
                $line[] = $s['percent'][$t->track()];
            }
            $c = $s['certificate'];
            array_push($line, $s['quizzes_passed'], $s['attempts'], $c['code'] ?? '', $c['date'] ?? '', $c['track'] ?? '');
            fputcsv($out, array_map('admin_csv_cell', $line), ',', '"', '');
        }
        if (count($rows) < $batch) {
            break;
        }
    }
    fclose($out);
}
