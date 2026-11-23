<?php
/**
 * Course structure and slide content.
 *
 * Each section: key, title, lessons.
 * Each lesson: key (unique, never rename once learners have progress), title,
 * type ('presentation' | 'quiz'), and for presentations a list of slides.
 * Each slide: ['title' => ..., 'body' => ...] and optionally 'image' => 'assets/slides/file.png'
 * (path relative to public/). When 'image' is set it is shown instead of the text.
 *
 * quiz_requires: section keys whose presentation lessons must be completed before the final quiz opens.
 */

$placeholderSlides = static function (string $topic, int $count): array {
    $slides = [];
    for ($i = 1; $i <= $count; $i++) {
        $slides[] = [
            'title' => $topic . ' - Slaid ' . $i,
            'body'  => 'Kandungan contoh untuk slaid ' . $i . ' bagi topik "' . $topic . '". '
                . 'Gantikan teks ini dengan kandungan sebenar modul.',
        ];
    }
    return $slides;
};

return [
    'key'           => 'ai-essentials',
    'title'         => 'Malaysia AWS AI Essentials',
    'quiz_requires' => ['modul-1', 'modul-2', 'modul-3', 'modul-4', 'modul-5'],
    'sections'      => [
        [
            'key'     => 'pre-survey',
            'title'   => 'Pre-Survey',
            'lessons' => [
                ['key' => 'pre-survey', 'title' => 'Pre-Survey', 'type' => 'presentation',
                 'slides' => $placeholderSlides('Pre-Survey', 1)],
            ],
        ],
        [
            'key'     => 'modul-1',
            'title'   => 'Modul 1: Kesedaran AI & Keyakinan Digital',
            'lessons' => [
                ['key' => 'modul-1-pengenalan', 'title' => 'Pengenalan AI', 'type' => 'presentation',
                 'slides' => $placeholderSlides('Pengenalan AI', 5)],
            ],
        ],
        [
            'key'     => 'modul-2',
            'title'   => 'Modul 2: Asas AI',
            'lessons' => [
                ['key' => 'modul-2-pengenalan', 'title' => 'Asas AI', 'type' => 'presentation',
                 'slides' => $placeholderSlides('Asas AI', 5)],
            ],
        ],
        [
            'key'     => 'modul-3',
            'title'   => 'Modul 3: Kemahiran Praktikal AI',
            'lessons' => [
                ['key' => 'modul-3-pengenalan', 'title' => 'Kemahiran Praktikal AI', 'type' => 'presentation',
                 'slides' => $placeholderSlides('Kemahiran Praktikal AI', 5)],
            ],
        ],
        [
            'key'     => 'modul-4',
            'title'   => 'Modul 4: AI untuk Perniagaan Mikro & Produktiviti',
            'lessons' => [
                ['key' => 'modul-4-pengenalan', 'title' => 'AI untuk Perniagaan Mikro', 'type' => 'presentation',
                 'slides' => $placeholderSlides('AI untuk Perniagaan Mikro', 5)],
            ],
        ],
        [
            'key'     => 'modul-5',
            'title'   => 'Modul 5: AI Bertanggungjawab & Kesediaan Masa Hadapan',
            'lessons' => [
                ['key' => 'modul-5-pengenalan', 'title' => 'AI Bertanggungjawab', 'type' => 'presentation',
                 'slides' => $placeholderSlides('AI Bertanggungjawab', 5)],
            ],
        ],
        [
            'key'     => 'kuiz-akhir',
            'title'   => 'Kuiz Akhir',
            'lessons' => [
                ['key' => 'kuiz-akhir', 'title' => 'Kuiz Akhir', 'type' => 'quiz'],
            ],
        ],
        [
            'key'     => 'post-survey',
            'title'   => 'Post-Survey',
            'lessons' => [
                ['key' => 'post-survey', 'title' => 'Post-Survey', 'type' => 'presentation',
                 'slides' => $placeholderSlides('Post-Survey', 1)],
            ],
        ],
        [
            'key'     => 'bonus',
            'title'   => 'Bonus Konten!',
            'lessons' => [
                ['key' => 'bonus-konten', 'title' => 'Bonus Konten!', 'type' => 'presentation',
                 'slides' => $placeholderSlides('Bonus Konten', 2)],
            ],
        ],
    ],
];
