<?php
declare(strict_types=1);

/** Read-only view of content/course.php and content/quiz.php. */
final class Course
{
    /** quiz_key of attempts from the old single final quiz (Kuiz Akhir, all 10 questions). */
    public const LEGACY_QUIZ_KEY = 'kuiz-akhir';

    /** @var array */
    private $data;
    /** @var array|null */
    private $quizData = null;
    /** @var array|null */
    private $flat = null;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function load(): self
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self(require APP_ROOT . '/content/course.php');
        }
        return $instance;
    }

    public function key(): string
    {
        return (string)$this->data['key'];
    }

    public function title(): string
    {
        return (string)$this->data['title'];
    }

    public function sections(): array
    {
        return $this->data['sections'];
    }

    /** All lessons in order, each with 'section_key'. */
    public function lessons(): array
    {
        if ($this->flat === null) {
            $this->flat = [];
            foreach ($this->data['sections'] as $section) {
                foreach ($section['lessons'] as $lesson) {
                    $lesson['section_key'] = $section['key'];
                    $this->flat[] = $lesson;
                }
            }
        }
        return $this->flat;
    }

    public function lesson(string $key): ?array
    {
        foreach ($this->lessons() as $lesson) {
            if ($lesson['key'] === $key) {
                return $lesson;
            }
        }
        return null;
    }

    private function offset(string $key, int $delta): ?array
    {
        $lessons = $this->lessons();
        foreach ($lessons as $i => $lesson) {
            if ($lesson['key'] === $key) {
                return $lessons[$i + $delta] ?? null;
            }
        }
        return null;
    }

    public function nextLesson(string $key): ?array
    {
        return $this->offset($key, 1);
    }

    public function prevLesson(string $key): ?array
    {
        return $this->offset($key, -1);
    }

    public function sectionLessons(string $sectionKey): array
    {
        return array_values(array_filter(
            $this->lessons(),
            static function (array $l) use ($sectionKey): bool {
                return $l['section_key'] === $sectionKey;
            }
        ));
    }

    /** Quiz lessons in course order. */
    public function quizLessons(): array
    {
        return array_values(array_filter(
            $this->lessons(),
            static function (array $l): bool {
                return $l['type'] === 'quiz';
            }
        ));
    }

    /** Pass mark (percent) for every module quiz. */
    public function passMark(): int
    {
        return (int)$this->quizData()['pass_mark'];
    }

    /**
     * The quiz of a quiz lesson: ['title', 'pass_mark', 'questions'].
     * Throws when $lessonKey is not a quiz lesson or its quiz is missing from content/quiz.php.
     */
    public function quiz(string $lessonKey): array
    {
        $lesson = $this->lesson($lessonKey);
        if ($lesson === null || $lesson['type'] !== 'quiz') {
            throw new RuntimeException('Not a quiz lesson: ' . $lessonKey);
        }
        $data = $this->quizData();
        $quiz = $data['quizzes'][$lesson['quiz'] ?? ''] ?? null;
        if (!is_array($quiz)) {
            throw new RuntimeException('Quiz not found for lesson: ' . $lessonKey);
        }
        return [
            'title'     => (string)$quiz['title'],
            'pass_mark' => (int)$data['pass_mark'],
            'questions' => $quiz['questions'],
        ];
    }

    private function quizData(): array
    {
        if ($this->quizData === null) {
            $this->quizData = require APP_ROOT . '/content/quiz.php';
        }
        return $this->quizData;
    }

    /**
     * Passed quiz lessons as a map lesson key => true.
     * A learner with a passed legacy Kuiz Akhir attempt, or with a completion, has passed every module quiz.
     *
     * @param string[] $passedQuizKeys quiz_key values of the learner's passed attempts
     */
    public function effectivePassed(array $passedQuizKeys, bool $completed): array
    {
        $all = array_map(static function (array $l): string {
            return $l['key'];
        }, $this->quizLessons());
        if ($completed || in_array(self::LEGACY_QUIZ_KEY, $passedQuizKeys, true)) {
            return array_fill_keys($all, true);
        }
        return array_fill_keys(array_values(array_intersect($all, $passedQuizKeys)), true);
    }

    /** @param array $passed map from effectivePassed() */
    public function allQuizzesPassed(array $passed): bool
    {
        return $this->firstUnpassedQuiz($passed) === null;
    }

    /** @param array $passed map from effectivePassed() */
    public function firstUnpassedQuiz(array $passed): ?array
    {
        foreach ($this->quizLessons() as $l) {
            if (!isset($passed[$l['key']])) {
                return $l;
            }
        }
        return null;
    }

    /**
     * Why a lesson is locked, or null when it is open.
     * Unlock order follows the sections: sections before the first quiz are always open; a later section opens
     * when the quiz before it is passed; a quiz opens when the other lessons of its section are done; sections
     * after the last quiz open when every quiz is passed.
     *
     * @param array $done   map lesson key => true (completed lessons)
     * @param array $passed map from effectivePassed()
     * @return array{message: string, links: array}|null
     */
    public function lockReason(array $lesson, array $done, array $passed): ?array
    {
        $quizzes = $this->quizLessons();
        if ($quizzes === []) {
            return null;
        }
        $sectionIndex = [];
        foreach ($this->data['sections'] as $i => $section) {
            $sectionIndex[$section['key']] = $i;
        }
        $own = $sectionIndex[$lesson['section_key']];
        $prevQuiz = null;
        foreach ($quizzes as $q) {
            if ($sectionIndex[$q['section_key']] < $own) {
                $prevQuiz = $q;
            }
        }
        $last = $quizzes[count($quizzes) - 1];
        if ($own > $sectionIndex[$last['section_key']]) {
            $first = $this->firstUnpassedQuiz($passed);
            if ($first !== null) {
                return [
                    'message' => 'Penutup dibuka selepas anda lulus kesemua kuiz modul (Modul 1 hingga Modul 5).',
                    'links'   => [$first],
                ];
            }
            return null;
        }
        if ($prevQuiz !== null && !isset($passed[$prevQuiz['key']])) {
            return [
                'message' => 'Modul ini dikunci. Sila lulus ' . $prevQuiz['title'] . ' terlebih dahulu.',
                'links'   => [$prevQuiz],
            ];
        }
        if ($lesson['type'] === 'quiz') {
            $missing = [];
            foreach ($this->sectionLessons($lesson['section_key']) as $l) {
                if ($l['type'] !== 'quiz' && !isset($done[$l['key']])) {
                    $missing[] = $l;
                }
            }
            if ($missing !== []) {
                $section = $this->data['sections'][$own];
                return [
                    'message' => 'Sila lengkapkan semua pelajaran dalam ' . $section['title'] . ' sebelum menjawab kuiz ini.',
                    'links'   => $missing,
                ];
            }
        }
        return null;
    }

    public function lessonLabel(array $lesson): string
    {
        if ($lesson['type'] === 'quiz') {
            return 'KUIZ - ' . count($this->quiz($lesson['key'])['questions']) . ' SOALAN';
        }
        return 'PEMBENTANGAN';
    }
}
