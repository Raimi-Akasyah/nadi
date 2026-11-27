<?php
/** Descriptions of the learning tracks for the landing page and "Pilih Cara Belajar" (views/pilih.php). */
if (!function_exists('track_info')) {
    /** @return array{icon: string, tagline: string, text: string, facts: string[], cta: string} */
    function track_info(Course $course): array
    {
        $quizzes = $course->quizLessons();
        $questions = 0;
        foreach ($quizzes as $q) {
            $questions += count($course->quiz($q['key'])['questions']);
        }
        if ($course->track() === 'self-learning') {
            $videos = array_values(array_filter($course->lessons(), static function (array $l): bool {
                return $l['type'] === 'video';
            }));
            $seconds = array_sum(array_map(static function (array $l): int {
                return (int)$l['duration'];
            }, $videos));
            return [
                'icon'    => 'video',
                'tagline' => 'Belajar dengan video',
                'text'    => 'Tonton video kursus sampai habis, kemudian jawab satu kuiz yang merangkumi semua modul.',
                'facts'   => [
                    count($videos) . ' video (' . format_duration($seconds) . ')',
                    count($quizzes) . ' kuiz, ' . $questions . ' soalan',
                ],
                'cta'     => 'Self-Learning',
            ];
        }
        return [
            'icon'    => 'slides',
            'tagline' => 'Belajar dengan slaid',
            'text'    => 'Ikuti slaid setiap modul mengikut kadar anda. Setiap modul ada kuiz pendek, dan modul seterusnya dibuka selepas anda lulus.',
            'facts'   => [
                count($quizzes) . ' modul',
                count($quizzes) . ' kuiz, ' . $questions . ' soalan',
            ],
            'cta'     => 'E-Learning',
        ];
    }
}
