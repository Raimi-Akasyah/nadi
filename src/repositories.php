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
