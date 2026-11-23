<?php
/**
 * Final quiz (Kuiz Akhir).
 *
 * How to replace the questions:
 * - Edit the 'questions' list below. Keep exactly the shape shown:
 *   ['id' => 'q1', 'text' => '...', 'options' => ['a' => '...', 'b' => '...', 'c' => '...', 'd' => '...'], 'answer' => 'b']
 * - 'id' must be unique. 'answer' must be one of the option keys.
 * - 'pass_mark' is the minimum percentage to pass (percent = correct * 100 / total).
 * - The correct answers are never sent to the browser.
 */
return [
    'title'     => 'Kuiz Akhir',
    'pass_mark' => 75,
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
];
