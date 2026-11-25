<?php
declare(strict_types=1);

/** Current local time for DB timestamps (never DB NOW()). */
function now_str(): string
{
    return date('Y-m-d H:i:s');
}

function learner_create(array $clean, string $tokenHash): int
{
    $stmt = db()->prepare(
        'INSERT INTO learners (first_name, last_name, age, email, phone, address, occupation, token_hash, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $clean['first_name'],
        $clean['last_name'],
        (int)$clean['age'],
        $clean['email'],
        $clean['phone'],
        $clean['address'],
        $clean['occupation'],
        $tokenHash,
        now_str(),
    ]);
    return (int)db()->lastInsertId();
}

function learner_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM learners WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/** The learner for a remember-cookie token hash: the registration token or one added on resume. */
function learner_find_by_token_hash(string $h): ?array
{
    $stmt = db()->prepare('SELECT * FROM learners WHERE token_hash = ?');
    $stmt->execute([$h]);
    $row = $stmt->fetch();
    if ($row !== false) {
        return $row;
    }
    $stmt = db()->prepare(
        'SELECT l.* FROM learner_tokens t JOIN learners l ON l.id = t.learner_id WHERE t.token_hash = ?'
    );
    $stmt->execute([$h]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/** Add a remember-cookie token for a learner (used when resuming with "Ingat saya" ticked). */
function learner_token_add(int $learnerId, string $tokenHash): void
{
    $stmt = db()->prepare('INSERT INTO learner_tokens (learner_id, token_hash, created_at) VALUES (?, ?, ?)');
    $stmt->execute([$learnerId, $tokenHash, now_str()]);
}

/**
 * The learner to resume for an e-mail address (case-insensitive), or null.
 * Earlier testing may have left several learners with one e-mail. The one with the most progress wins:
 * a course completion first, then a passed quiz, then the most completed lessons, then the newest record.
 */
function learner_find_for_resume(string $email): ?array
{
    $stmt = db()->prepare(
        'SELECT l.*,
                (SELECT COUNT(*) FROM completions c WHERE c.learner_id = l.id) AS resume_completions,
                (SELECT COUNT(DISTINCT a.quiz_key) FROM quiz_attempts a WHERE a.learner_id = l.id AND a.passed = 1) AS resume_passes,
                (SELECT COUNT(*) FROM lesson_progress p WHERE p.learner_id = l.id) AS resume_lessons
         FROM learners l
         WHERE LOWER(l.email) = LOWER(?)
         ORDER BY resume_completions DESC, resume_passes DESC, resume_lessons DESC, l.id DESC
         LIMIT 1'
    );
    $stmt->execute([trim($email)]);
    $row = $stmt->fetch();
    if ($row === false) {
        return null;
    }
    unset($row['resume_completions'], $row['resume_passes'], $row['resume_lessons']);
    return $row;
}

/** Number of resume attempts since $since from this IP hash and for this e-mail hash. */
function resume_attempt_counts(string $ipHash, string $emailHash, string $since): array
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM resume_attempts WHERE ip_hash = ? AND created_at >= ?');
    $stmt->execute([$ipHash, $since]);
    $ip = (int)$stmt->fetchColumn();
    $stmt = db()->prepare('SELECT COUNT(*) FROM resume_attempts WHERE email_hash = ? AND created_at >= ?');
    $stmt->execute([$emailHash, $since]);
    return ['ip' => $ip, 'email' => (int)$stmt->fetchColumn()];
}

/** Record a resume attempt and drop attempts older than one day. */
function resume_attempt_record(string $ipHash, string $emailHash): void
{
    db()->prepare('DELETE FROM resume_attempts WHERE created_at < ?')
        ->execute([date('Y-m-d H:i:s', time() - 86400)]);
    db()->prepare('INSERT INTO resume_attempts (ip_hash, email_hash, created_at) VALUES (?, ?, ?)')
        ->execute([$ipHash, $emailHash, now_str()]);
}

function progress_mark(int $learnerId, string $lessonKey): void
{
    $stmt = db()->prepare(
        'INSERT IGNORE INTO lesson_progress (learner_id, lesson_key, completed_at) VALUES (?, ?, ?)'
    );
    $stmt->execute([$learnerId, $lessonKey, now_str()]);
}

/** @return string[] completed lesson keys */
function progress_keys(int $learnerId): array
{
    $stmt = db()->prepare('SELECT lesson_key FROM lesson_progress WHERE learner_id = ?');
    $stmt->execute([$learnerId]);
    return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/** Store a graded attempt (result from grade_quiz) of one quiz ($quizKey = quiz lesson key) and return its id. */
function attempt_create(int $learnerId, string $courseKey, string $quizKey, array $result): int
{
    $stmt = db()->prepare(
        'INSERT INTO quiz_attempts (learner_id, course_key, quiz_key, correct_count, total_questions, score_percent, passed, answers_json, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $learnerId,
        $courseKey,
        $quizKey,
        (int)$result['correct'],
        (int)$result['total'],
        number_format((float)$result['percent'], 2, '.', ''),
        $result['passed'] ? 1 : 0,
        json_encode($result['answers'], JSON_UNESCAPED_UNICODE),
        now_str(),
    ]);
    return (int)db()->lastInsertId();
}

/** An attempt by id, only if it belongs to the learner. */
function attempt_find(int $id, int $learnerId): ?array
{
    $stmt = db()->prepare('SELECT * FROM quiz_attempts WHERE id = ? AND learner_id = ?');
    $stmt->execute([$id, $learnerId]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function attempt_latest_passed(int $learnerId, string $courseKey): ?array
{
    $stmt = db()->prepare(
        'SELECT * FROM quiz_attempts WHERE learner_id = ? AND course_key = ? AND passed = 1 ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([$learnerId, $courseKey]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/** Number of attempts of one quiz. */
function attempt_count(int $learnerId, string $courseKey, string $quizKey): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE learner_id = ? AND course_key = ? AND quiz_key = ?');
    $stmt->execute([$learnerId, $courseKey, $quizKey]);
    return (int)$stmt->fetchColumn();
}

/** @return string[] quiz keys with at least one passed attempt (may include the legacy 'kuiz-akhir') */
function quiz_passed_keys(int $learnerId, string $courseKey): array
{
    $stmt = db()->prepare('SELECT DISTINCT quiz_key FROM quiz_attempts WHERE learner_id = ? AND course_key = ? AND passed = 1');
    $stmt->execute([$learnerId, $courseKey]);
    return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function completion_find(int $learnerId, string $courseKey): ?array
{
    $stmt = db()->prepare('SELECT * FROM completions WHERE learner_id = ? AND course_key = ?');
    $stmt->execute([$learnerId, $courseKey]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/**
 * Create the learner's completion with a unique certificate code.
 * If one already exists it is returned unchanged (the original date is kept).
 */
function completion_create(int $learnerId, string $courseKey, int $attemptId): array
{
    $existing = completion_find($learnerId, $courseKey);
    if ($existing !== null) {
        return $existing;
    }
    $stmt = db()->prepare(
        'INSERT INTO completions (learner_id, course_key, quiz_attempt_id, certificate_code, completed_at)
         VALUES (?, ?, ?, ?, ?)'
    );
    for ($try = 1; $try <= 3; $try++) {
        $code = 'NADI-' . strtoupper(bin2hex(random_bytes(5)));
        try {
            $stmt->execute([$learnerId, $courseKey, $attemptId, $code, now_str()]);
            return completion_find($learnerId, $courseKey);
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            // Either a concurrent request completed first, or the code collided.
            $existing = completion_find($learnerId, $courseKey);
            if ($existing !== null) {
                return $existing;
            }
            if ($try === 3) {
                throw $ex;
            }
        }
    }
    throw new RuntimeException('Could not create completion.');
}

/** A completion with learner names, looked up by certificate code. */
function completion_find_by_code(string $code): ?array
{
    $stmt = db()->prepare(
        'SELECT c.*, l.first_name, l.last_name
         FROM completions c JOIN learners l ON l.id = c.learner_id
         WHERE c.certificate_code = ?'
    );
    $stmt->execute([$code]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}
