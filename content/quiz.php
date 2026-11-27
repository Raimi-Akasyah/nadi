<?php
/**
 * Module quizzes: one quiz at the end of each module (Modul 1 to Modul 5).
 *
 * Each key in 'quizzes' (e.g. 'modul-1') belongs to the quiz lesson of that module in content/course.php
 * ('quiz' => 'modul-1'). A learner must pass every module quiz to finish the course and get the certificate.
 *
 * How to add or replace questions:
 * - Append to (or edit) the 'questions' list of that module. Keep exactly the shape shown:
 *   ['id' => 'q11', 'text' => '...', 'options' => ['a' => '...', 'b' => '...', 'c' => '...', 'd' => '...'], 'answer' => 'b']
 * - 'id' must be unique across ALL quizzes in this file. 'answer' must be one of the option keys (a-d).
 * - 'pass_mark' is the minimum percentage to pass each module quiz (percent = correct * 100 / total).
 *   With 1 to 3 questions a pass mark of 75% means every answer must be correct, so 4 or more
 *   questions per module are recommended.
 * - The correct answers are never sent to the browser.
 *
 * - 'self-learning' (the quiz of the Self-Learning track, content/self-learning.php) has no questions of its own:
 *   'from' lists the module quizzes whose questions it asks, in that order. A new module question is therefore
 *   asked in both tracks.
 *
 * Keep docs/assets/js/app.js (QUIZ) in sync with this file.
 */
return [
    'pass_mark' => 75,
    'quizzes'   => [
        'modul-1' => [
            'title'     => 'Kuiz Modul 1',
            'questions' => [
                [
                    'id'      => 'q1',
                    'text'    => 'Apakah maksud singkatan "AI"?',
                    'options' => [
                        'a' => 'Automasi Internet',
                        'b' => 'Kecerdasan Buatan (Artificial Intelligence)',
                        'c' => 'Aplikasi Interaktif',
                        'd' => 'Analisis Imej',
                    ],
                    'answer'  => 'b',
                ],
                [
                    'id'      => 'q2',
                    'text'    => 'Antara berikut, yang manakah contoh penggunaan AI dalam kehidupan harian?',
                    'options' => [
                        'a' => 'Kalkulator biasa',
                        'b' => 'Lampu suluh',
                        'c' => 'Cadangan video di aplikasi penstriman',
                        'd' => 'Jam dinding',
                    ],
                    'answer'  => 'c',
                ],
                [
                    'id'      => 'q3',
                    'text'    => 'Apakah langkah terbaik untuk meningkatkan keyakinan digital?',
                    'options' => [
                        'a' => 'Mengelak semua teknologi baharu',
                        'b' => 'Berkongsi kata laluan dengan rakan',
                        'c' => 'Belajar dan mencuba alat digital secara berperingkat',
                        'd' => 'Hanya bergantung kepada orang lain',
                    ],
                    'answer'  => 'c',
                ],
            ],
        ],
        'modul-2' => [
            'title'     => 'Kuiz Modul 2',
            'questions' => [
                [
                    'id'      => 'q4',
                    'text'    => 'Pembelajaran mesin (machine learning) ialah...',
                    'options' => [
                        'a' => 'Cabang AI di mana komputer belajar daripada data',
                        'b' => 'Proses membaiki mesin rosak',
                        'c' => 'Kursus menaip pantas',
                        'd' => 'Perisian antivirus',
                    ],
                    'answer'  => 'a',
                ],
                [
                    'id'      => 'q5',
                    'text'    => 'Apakah yang diperlukan oleh model AI untuk belajar?',
                    'options' => [
                        'a' => 'Elektrik sahaja',
                        'b' => 'Data',
                        'c' => 'Kertas',
                        'd' => 'Tiada apa-apa',
                    ],
                    'answer'  => 'b',
                ],
            ],
        ],
        'modul-3' => [
            'title'     => 'Kuiz Modul 3',
            'questions' => [
                [
                    'id'      => 'q6',
                    'text'    => 'Apakah "prompt" dalam konteks alat AI generatif?',
                    'options' => [
                        'a' => 'Arahan atau soalan yang diberikan kepada AI',
                        'b' => 'Jenis virus komputer',
                        'c' => 'Butang untuk menutup aplikasi',
                        'd' => 'Kabel sambungan internet',
                    ],
                    'answer'  => 'a',
                ],
                [
                    'id'      => 'q7',
                    'text'    => 'Bagaimanakah cara terbaik untuk mendapatkan jawapan yang lebih tepat daripada AI?',
                    'options' => [
                        'a' => 'Menulis arahan yang ringkas tetapi kabur',
                        'b' => 'Memberikan arahan yang jelas dan konteks yang mencukupi',
                        'c' => 'Menggunakan huruf besar sahaja',
                        'd' => 'Bertanya soalan yang sama berulang kali tanpa perubahan',
                    ],
                    'answer'  => 'b',
                ],
            ],
        ],
        'modul-4' => [
            'title'     => 'Kuiz Modul 4',
            'questions' => [
                [
                    'id'      => 'q8',
                    'text'    => 'Bagaimanakah AI boleh membantu perniagaan mikro?',
                    'options' => [
                        'a' => 'Menggantikan semua pelanggan',
                        'b' => 'Menghasilkan kandungan pemasaran dan menjimatkan masa',
                        'c' => 'Menghapuskan keperluan menyimpan rekod',
                        'd' => 'Menjamin keuntungan setiap bulan',
                    ],
                    'answer'  => 'b',
                ],
            ],
        ],
        'modul-5' => [
            'title'     => 'Kuiz Modul 5',
            'questions' => [
                [
                    'id'      => 'q9',
                    'text'    => 'Apakah yang perlu dilakukan sebelum berkongsi maklumat yang dihasilkan oleh AI?',
                    'options' => [
                        'a' => 'Terus kongsi tanpa semakan',
                        'b' => 'Padam semua sumber',
                        'c' => 'Semak ketepatan maklumat tersebut',
                        'd' => 'Tukar fon tulisan',
                    ],
                    'answer'  => 'c',
                ],
                [
                    'id'      => 'q10',
                    'text'    => 'Antara berikut, yang manakah amalan AI yang bertanggungjawab?',
                    'options' => [
                        'a' => 'Memasukkan data peribadi orang lain tanpa izin',
                        'b' => 'Menggunakan AI untuk menyebarkan berita palsu',
                        'c' => 'Melindungi privasi dan menggunakan AI secara beretika',
                        'd' => 'Mempercayai semua output AI sepenuhnya',
                    ],
                    'answer'  => 'c',
                ],
            ],
        ],
        'self-learning' => [
            'title' => 'Kuiz Self-Learning',
            'from'  => ['modul-1', 'modul-2', 'modul-3', 'modul-4', 'modul-5'],
        ],
    ],
];
