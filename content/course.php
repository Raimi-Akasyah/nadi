<?php
/**
 * E-Learning track: course structure and slide content.
 * (The Self-Learning track, with a video instead of slides, is in content/self-learning.php. Both tracks share
 * the course 'key' and 'title', so they share one completion and one certificate.)
 *
 * Each section: key, title, lessons.
 * Each lesson: key (unique, never rename once learners have progress), title,
 * type ('presentation' | 'quiz'), and for presentations a list of slides.
 * Each slide: ['title' => ..., 'image' => 'assets/slides/page-NN.webp'] where NN is the page
 * number in the course deck PDF (path relative to public/; identical copies live in
 * docs/assets/slides/). The title is the image alt text. A slide without 'image' shows
 * 'title' and 'body' as text instead.
 *
 * Keep docs/assets/js/app.js (COURSE) in sync with this file.
 *
 * Quiz lessons: ['key' => 'kuiz-modul-N', 'title' => 'Kuiz Modul N', 'type' => 'quiz', 'quiz' => 'modul-N'] where
 * 'quiz' is a key of 'quizzes' in content/quiz.php. Each module ends with its quiz lesson.
 *
 * Unlock order = section order. Sections before the first quiz are always open. A section with a quiz opens
 * when the quiz of the section before it is passed (the first one is open). A quiz opens when the other lessons
 * of its section are done. Sections after the last quiz open when every quiz is passed.
 */
return [
    'key'         => 'ai-essentials',
    'title'       => 'Malaysia AWS AI Essentials',
    'track'       => 'e-learning',
    'track_title' => 'E-Learning',
    'sections'    => [
        [
            'key'     => 'pengenalan',
            'title'   => 'Pengenalan',
            'lessons' => [
                ['key' => 'pengenalan', 'title' => 'Pengenalan', 'type' => 'presentation', 'slides' => [
                    ['title' => 'AI Untuk Komuniti', 'image' => 'assets/slides/page-01.webp'],
                    ['title' => 'Rakan Strategik Program: AWS', 'image' => 'assets/slides/page-07.webp'],
                ]],
            ],
        ],
        [
            'key'     => 'modul-1',
            'title'   => 'Modul 1: Kesedaran AI & Keyakinan Digital',
            'lessons' => [
                ['key' => 'modul-1-pengenalan', 'title' => 'Pengenalan AI', 'type' => 'presentation', 'slides' => [
                    ['title' => 'Modul 1: Kesedaran AI & Keyakinan Digital', 'image' => 'assets/slides/page-08.webp'],
                    ['title' => 'Apakah Itu Kecerdasan Buatan (AI)?', 'image' => 'assets/slides/page-09.webp'],
                    ['title' => 'AI Bukan Pengganti Kita, Tapi Pembantu Kita', 'image' => 'assets/slides/page-10.webp'],
                ]],
                ['key' => 'kuiz-modul-1', 'title' => 'Kuiz Modul 1', 'type' => 'quiz', 'quiz' => 'modul-1'],
            ],
        ],
        [
            'key'     => 'modul-2',
            'title'   => 'Modul 2: Asas AI',
            'lessons' => [
                ['key' => 'modul-2-pengenalan', 'title' => 'Asas AI', 'type' => 'presentation', 'slides' => [
                    ['title' => 'Modul 2: Asas AI', 'image' => 'assets/slides/page-11.webp'],
                    ['title' => 'Macam Mana AI Belajar?', 'image' => 'assets/slides/page-12.webp'],
                    ['title' => 'Apakah Itu AI Generatif?', 'image' => 'assets/slides/page-13.webp'],
                    ['title' => "'Perangai' Pekerja Baru AI Kita", 'image' => 'assets/slides/page-14.webp'],
                ]],
                ['key' => 'kuiz-modul-2', 'title' => 'Kuiz Modul 2', 'type' => 'quiz', 'quiz' => 'modul-2'],
            ],
        ],
        [
            'key'     => 'modul-3',
            'title'   => 'Modul 3: Kemahiran Praktikal AI',
            'lessons' => [
                ['key' => 'modul-3-pengenalan', 'title' => 'Kemahiran Praktikal AI', 'type' => 'presentation', 'slides' => [
                    ['title' => 'Modul 3: Kemahiran Praktikal AI untuk Pembelajaran & Kerjaya', 'image' => 'assets/slides/page-15.webp'],
                    ['title' => 'Apa Itu "AI Prompt"?', 'image' => 'assets/slides/page-16.webp'],
                    ['title' => 'Mengapa Arahan yang Jelas Sangat Penting?', 'image' => 'assets/slides/page-17.webp'],
                    ['title' => 'Selesaikan Masalah dengan Formula KOPI!', 'image' => 'assets/slides/page-18.webp'],
                    ['title' => '4 Ramuan Rahsia dalam Secawan KOPI', 'image' => 'assets/slides/page-19.webp'],
                    ['title' => 'Transformasi Prompt: Pemasaran Tudung Raya', 'image' => 'assets/slides/page-20.webp'],
                    ['title' => '3 Peranan Utama AI untuk Bisnes Anda', 'image' => 'assets/slides/page-21.webp'],
                    ['title' => "Latihan Berkumpulan: Selesaikan Krisis 'Sales Turun' (bisnes.ai)", 'image' => 'assets/slides/page-22.webp'],
                    ['title' => "Latihan Berkumpulan: Selesaikan Krisis 'Sales Turun' (3 Langkah)", 'image' => 'assets/slides/page-23.webp'],
                ]],
                ['key' => 'kuiz-modul-3', 'title' => 'Kuiz Modul 3', 'type' => 'quiz', 'quiz' => 'modul-3'],
            ],
        ],
        [
            'key'     => 'modul-4',
            'title'   => 'Modul 4: AI untuk Perniagaan Mikro & Produktiviti',
            'lessons' => [
                ['key' => 'modul-4-pengenalan', 'title' => 'AI untuk Perniagaan Mikro', 'type' => 'presentation', 'slides' => [
                    ['title' => 'Modul 4: AI untuk Perniagaan Mikro & Produktiviti', 'image' => 'assets/slides/page-24.webp'],
                    ['title' => 'Janji 45 Minit Anda', 'image' => 'assets/slides/page-25.webp'],
                    ['title' => 'Langkah 1: Jana Idea Perniagaan', 'image' => 'assets/slides/page-26.webp'],
                    ['title' => 'Langkah 1: Jana Idea Perniagaan (Kanvas Model Perniagaan)', 'image' => 'assets/slides/page-27.webp'],
                    ['title' => 'Langkah 2: Tulis Copywriting Media Sosial', 'image' => 'assets/slides/page-28.webp'],
                    ['title' => 'Langkah 2: Tulis Copywriting Media Sosial (Prompt KOPI)', 'image' => 'assets/slides/page-29.webp'],
                    ['title' => 'Langkah 4: Visual Poster', 'image' => 'assets/slides/page-30.webp'],
                    ['title' => 'Langkah 4: Visual Poster (Contoh Prompt)', 'image' => 'assets/slides/page-31.webp'],
                    ['title' => 'Langkah 5: Komunikasi Pelanggan Profesional', 'image' => 'assets/slides/page-32.webp'],
                    ['title' => 'Langkah 6: Skrip Video Promosi (30 Saat)', 'image' => 'assets/slides/page-33.webp'],
                    ['title' => 'Langkah 6: Skrip Video Promosi (Prompt KOPI)', 'image' => 'assets/slides/page-34.webp'],
                    ['title' => 'Jom Hasilkan Aplikasi (PartyRock)', 'image' => 'assets/slides/page-35.webp'],
                ]],
                ['key' => 'kuiz-modul-4', 'title' => 'Kuiz Modul 4', 'type' => 'quiz', 'quiz' => 'modul-4'],
            ],
        ],
        [
            'key'     => 'modul-5',
            'title'   => 'Modul 5: AI Bertanggungjawab & Kesediaan Masa Hadapan',
            'lessons' => [
                ['key' => 'modul-5-pengenalan', 'title' => 'AI Bertanggungjawab', 'type' => 'presentation', 'slides' => [
                    ['title' => 'Modul 5: AI Bertanggungjawab & Kesediaan Masa Hadapan', 'image' => 'assets/slides/page-36.webp'],
                    ['title' => '4 Tanda Amaran: Gunakan S.T.O.P', 'image' => 'assets/slides/page-37.webp'],
                    ['title' => 'Berhenti. Semak. Sahkan.', 'image' => 'assets/slides/page-38.webp'],
                    ['title' => 'Jana Pendapatan Tambahan dengan Bantuan AI', 'image' => 'assets/slides/page-39.webp'],
                    ['title' => "Apa Maksud 'Kerja Freelance'?", 'image' => 'assets/slides/page-40.webp'],
                    ['title' => 'Kenali 4 Alat AI yang Akan Membantu Anda', 'image' => 'assets/slides/page-41.webp'],
                    ['title' => 'Teruskan Pembelajaran Anda Selepas Bengkel Ini', 'image' => 'assets/slides/page-42.webp'],
                ]],
                ['key' => 'kuiz-modul-5', 'title' => 'Kuiz Modul 5', 'type' => 'quiz', 'quiz' => 'modul-5'],
            ],
        ],
        [
            'key'     => 'penutup',
            'title'   => 'Penutup',
            'lessons' => [
                ['key' => 'penutup', 'title' => 'Penutup', 'type' => 'presentation', 'slides' => [
                    ['title' => 'Terima Kasih', 'image' => 'assets/slides/page-45.webp'],
                ]],
            ],
        ],
    ],
];
