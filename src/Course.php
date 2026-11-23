<?php
declare(strict_types=1);

/** Read-only view of content/course.php and content/quiz.php. */
final class Course
{
    /** @var array */
    private $data;
    /** @var array|null */
    private $quiz = null;
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

    public function quizLesson(): array
    {
        foreach ($this->lessons() as $lesson) {
            if ($lesson['type'] === 'quiz') {
                return $lesson;
            }
        }
        throw new RuntimeException('Course has no quiz lesson.');
    }

    /** Presentation lessons of the sections listed in quiz_requires. */
    public function quizRequiredLessons(): array
    {
        $required = $this->data['quiz_requires'] ?? [];
        return array_values(array_filter(
            $this->lessons(),
            static function (array $l) use ($required): bool {
                return $l['type'] === 'presentation' && in_array($l['section_key'], $required, true);
            }
        ));
    }

    public function quiz(): array
    {
        if ($this->quiz === null) {
            $this->quiz = require APP_ROOT . '/content/quiz.php';
        }
        return $this->quiz;
    }

    public function lessonLabel(array $lesson): string
    {
        if ($lesson['type'] === 'quiz') {
            return 'KUIZ - ' . count($this->quiz()['questions']) . ' SOALAN';
        }
        return 'PEMBENTANGAN';
    }
}
