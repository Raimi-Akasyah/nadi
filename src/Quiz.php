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
