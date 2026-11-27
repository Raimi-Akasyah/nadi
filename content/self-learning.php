<?php
/**
 * Self-Learning track: one module with a video instead of slides. Flow: watch the video to the end -> Kuiz
 * Self-Learning -> certificate.
 *
 * 'key' and 'title' must stay the same as in content/course.php: both tracks share one completion and one
 * certificate, so finishing either track gives the certificate.
 *
 * Video lessons: ['key' => ..., 'title' => ..., 'type' => 'video', 'youtube' => '<11-character YouTube video id>',
 * 'duration' => <length in seconds>]. Upload the video to YouTube as "Unlisted" and copy the id from its link
 * (https://youtu.be/<id> or https://www.youtube.com/watch?v=<id>). 'duration' must match the video: the server
 * refuses "LENGKAP & TERUSKAN" until at least half of it has passed since the lesson was opened (the player itself
 * stops skipping ahead and allows playback up to 2x speed). Lesson keys are stored with learner progress, so keep
 * them unchanged once learners have started.
 *
 * Like E-Learning, the track opens with Pengenalan and ends with Penutup (both videos). Pengenalan and the main
 * video are open from the start; Kuiz Self-Learning opens when the main video is done; Penutup opens when Kuiz
 * Self-Learning is passed.
 *
 * The three videos below are PLACEHOLDERS (Blender Foundation open movies, CC BY: Caminandes 2, Big Buck Bunny,
 * Caminandes 3). Replace 'youtube' and 'duration' with the real course videos, and copy the change to SELF in
 * docs/assets/js/app.js.
 *
 * The quiz lesson uses 'quiz' => 'self-learning' in content/quiz.php, which reuses the questions of the five
 * module quizzes.
 */
return [
    'key'         => 'ai-essentials',
    'title'       => 'Malaysia AWS AI Essentials',
    'track'       => 'self-learning',
    'track_title' => 'Self-Learning',
    'sections'    => [
        [
            'key'     => 'pengenalan',
            'title'   => 'Pengenalan',
            'lessons' => [
                ['key' => 'self-pengenalan', 'title' => 'Video Pengenalan (Contoh)', 'type' => 'video',
                    'youtube' => 'Z4C82eyhwgU', 'duration' => 146],
            ],
        ],
        [
            'key'     => 'modul-1',
            'title'   => 'Modul 1: Video Kursus',
            'lessons' => [
                ['key' => 'self-video', 'title' => 'Video Kursus (Contoh)', 'type' => 'video',
                    'youtube' => 'aqz-KE-bpKQ', 'duration' => 635],
                ['key' => 'kuiz-self-learning', 'title' => 'Kuiz Self-Learning', 'type' => 'quiz', 'quiz' => 'self-learning'],
            ],
        ],
        [
            'key'     => 'penutup',
            'title'   => 'Penutup',
            'lessons' => [
                ['key' => 'self-penutup', 'title' => 'Video Penutup (Contoh)', 'type' => 'video',
                    'youtube' => 'SkVqJ1SGeL0', 'duration' => 150],
            ],
        ],
    ],
];
