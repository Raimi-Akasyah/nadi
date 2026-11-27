<?php
declare(strict_types=1);

/**
 * Grade a quiz submission. Pure function (no DB, no globals).
 *
 * @param array $quiz    quiz data as in content/quiz.php (questions + pass_mark)
 * @param array $answers submitted map question id => option key
 * @return array{correct:int,total:int,percent:float,passed:bool,answers:array<string,string>}
 */
function grade_quiz(array $quiz, array $answers): array
{
    $questions = $quiz['questions'] ?? [];
    $total = count($questions);
    $correct = 0;
    $clean = [];

    foreach ($questions as $q) {
        $qid = (string)$q['id'];
        $given = $answers[$qid] ?? null;
        if (!is_string($given) || !array_key_exists($given, $q['options'])) {
            continue;
        }
        $clean[$qid] = $given;
        if ($given === $q['answer']) {
            $correct++;
        }
    }

    $percent = $total > 0 ? round($correct * 100 / $total, 2) : 0.0;
    $passMark = (float)($quiz['pass_mark'] ?? 75);

    return [
        'correct' => $correct,
        'total'   => $total,
        'percent' => (float)$percent,
        'passed'  => $percent >= $passMark,
        'answers' => $clean,
    ];
}

/**
 * Per-question review of a graded attempt ("Semakan Jawapan" on the result page). Pure function.
 * The view shows 'answer_text' only when the attempt passed, so a failed attempt does not give away the answers.
 *
 * @param array $quiz    quiz data as in content/quiz.php (questions with answer and explanation)
 * @param array $answers stored answers of the attempt: question id => option key
 * @return list<array{id:string, text:string, given_text:?string, correct:bool, answer_text:string, explanation:string}>
 */
function quiz_review(array $quiz, array $answers): array
{
    $out = [];
    foreach ($quiz['questions'] ?? [] as $q) {
        $id = (string)$q['id'];
        $given = $answers[$id] ?? null;
        if (!is_string($given) || !array_key_exists($given, $q['options'])) {
            $given = null;
        }
        $out[] = [
            'id'          => $id,
            'text'        => (string)$q['text'],
            'given_text'  => $given !== null ? (string)$q['options'][$given] : null,
            'correct'     => $given !== null && $given === $q['answer'],
            'answer_text' => (string)$q['options'][$q['answer']],
            'explanation' => (string)($q['explanation'] ?? ''),
        ];
    }
    return $out;
}
