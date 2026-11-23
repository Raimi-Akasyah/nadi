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

function learner_find_by_token_hash(string $h): ?array
{
    $stmt = db()->prepare('SELECT * FROM learners WHERE token_hash = ?');
    $stmt->execute([$h]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
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

/** Store a graded quiz attempt (result from grade_quiz) and return its id. */
function attempt_create(int $learnerId, string $courseKey, array $result): int
{
    $stmt = db()->prepare(
        'INSERT INTO quiz_attempts (learner_id, course_key, correct_count, total_questions, score_percent, passed, answers_json, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $learnerId,
        $courseKey,
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

function attempt_count(int $learnerId, string $courseKey): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE learner_id = ? AND course_key = ?');
    $stmt->execute([$learnerId, $courseKey]);
    return (int)$stmt->fetchColumn();
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
