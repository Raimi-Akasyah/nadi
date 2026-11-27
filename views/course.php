<?php
/** @var Course $course */
/** @var Course[] $tracks */
/** @var array $learner */
/** @var array $lesson */
/** @var array $done */
/** @var int $slide */
/** @var int $slideCount */
/** @var int $percent */
/** @var array|null $lock */
/** @var array $passed */
/** @var bool $allPassed */
/** @var array|null $nextLesson */
/** @var array|null $quiz */
/** @var array|null $attempt */
/** @var int $attemptNo */
/** @var array|null $completion */
/** @var string|null $flash */
require_once __DIR__ . '/icons.php';

$lessonUrl = static function (string $key, ?int $slideNo = null): string {
    $q = ['lesson' => $key];
    if ($slideNo !== null) {
        $q['slide'] = $slideNo;
    }
    return 'course.php?' . http_build_query($q);
};
$sectionTitle = '';
foreach ($course->sections() as $section) {
    if ($section['key'] === $lesson['section_key']) {
        $sectionTitle = $section['title'];
        break;
    }
}
$initials = name_initials(learner_name($learner));
?>
<header class="topbar">
    <div class="topbar-left">
        <button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-controls="course-sidebar" aria-expanded="false">
            <?= icon('list') ?><span class="sidebar-toggle-text">Kandungan</span>
            <span class="sidebar-toggle-pct"><?= e($percent) ?>%</span>
        </button>
        <a class="topbar-home" href="index.php"><?= icon('home', 'topbar-home-icon') ?><span class="topbar-home-text">&larr; Pergi ke Laman Utama</span></a>
    </div>
    <a class="topbar-logo" href="index.php"><img src="assets/img/logo.svg" alt="NADI" width="96" height="32"></a>
    <div class="topbar-right">
        <span class="avatar avatar-sm" aria-hidden="true"><?= e($initials) ?></span>
        <span class="topbar-user"><?= e(learner_name($learner)) ?></span>
        <form method="post" action="reset.php" class="inline-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline btn-sm">Selesai / Keluar</button>
        </form>
    </div>
</header>

<div class="course-layout">
    <aside class="sidebar" id="course-sidebar" aria-label="Kandungan kursus">
        <h2 class="sidebar-title"><?= e($course->title()) ?></h2>
        <nav class="track-switch" aria-label="Cara belajar">
            <?php foreach ($tracks as $t):
                $isTrack = $t->track() === $course->track();
            ?>
                <a class="track-switch-btn<?= $isTrack ? ' is-active' : '' ?>" href="course.php?<?= e(http_build_query(['track' => $t->track()])) ?>"<?= $isTrack ? ' aria-current="true"' : '' ?>>
                    <?= icon($t->track() === 'self-learning' ? 'video' : 'slides') ?><?= e($t->trackTitle()) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="progress<?= $percent >= 100 ? ' is-complete' : '' ?>">
            <div class="progress-text"><?= e($percent) ?>% selesai</div>
            <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e($percent) ?>">
                <span style="width: <?= e($percent) ?>%"></span>
            </div>
        </div>
        <div class="search-field">
            <?= icon('search', 'search-icon') ?>
            <input type="search" id="lesson-search" class="sidebar-search" placeholder="Cari mengikut tajuk pelajaran"
                   aria-label="Cari mengikut tajuk pelajaran">
        </div>

        <nav class="sidebar-sections">
            <?php foreach ($course->sections() as $section):
                $sectionLessons = $course->sectionLessons($section['key']);
                $sectionDone = 0;
                foreach ($sectionLessons as $l) {
                    if (isset($done[$l['key']])) {
                        $sectionDone++;
                    }
                }
                $isCurrentSection = $section['key'] === $lesson['section_key'];
                $sectionLocked = $sectionLessons !== [] && $course->lockReason($sectionLessons[0], $done, $passed) !== null;
                $sectionComplete = $sectionLessons !== [] && $sectionDone === count($sectionLessons);
            ?>
                <details class="sidebar-section<?= $sectionComplete ? ' is-complete' : '' ?><?= $sectionLocked ? ' is-section-locked' : '' ?>" data-section="<?= e($section['key']) ?>"<?= $isCurrentSection ? ' open' : '' ?>>
                    <summary>
                        <span class="section-title"><?= e($section['title']) ?><?php if ($sectionLocked): ?><span class="section-lock" aria-hidden="true"><?= icon('lock') ?></span><span class="sr-only">(dikunci)</span><?php endif; ?></span>
                        <span class="section-count"><?= e($sectionDone) ?>/<?= e(count($sectionLessons)) ?></span>
                        <span class="section-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="lesson-list">
                        <?php foreach ($sectionLessons as $l):
                            $isDone = isset($done[$l['key']]);
                            $isCurrent = $l['key'] === $lesson['key'];
                            $isLocked = $course->lockReason($l, $done, $passed) !== null;
                            $isQuiz = $l['type'] === 'quiz';
                            $isPassed = $isQuiz && isset($passed[$l['key']]);
                        ?>
                            <li class="lesson-row<?= $isCurrent ? ' is-current' : '' ?><?= $isDone ? ' is-done' : '' ?><?= $isLocked ? ' is-locked' : '' ?>"
                                data-title="<?= e($l['title']) ?>">
                                <a href="<?= e($lessonUrl($l['key'])) ?>"<?= $isCurrent ? ' aria-current="page"' : '' ?>>
                                    <span class="lesson-tick" aria-hidden="true"><?= $isDone ? icon('check') : ($isLocked ? icon('lock') : '') ?></span>
                                    <span class="lesson-meta">
                                        <span class="lesson-title"><?= e($l['title']) ?></span>
                                        <span class="lesson-tags">
                                            <span class="lesson-label"><?= icon(Course::lessonIcon($l)) ?><?= e($course->lessonLabel($l)) ?></span>
                                            <?php if ($isQuiz): ?>
                                                <span class="lesson-status<?= $isPassed ? ' is-pass' : '' ?>"><?= $isPassed ? 'LULUS' : 'BELUM LULUS' ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </span>
                                    <?php if ($isDone): ?><span class="sr-only">(selesai)</span><?php endif; ?>
                                    <?php if ($isLocked): ?><span class="sr-only">(dikunci)</span><?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endforeach; ?>
        </nav>
    </aside>
    <button type="button" class="sidebar-backdrop" aria-label="Tutup kandungan kursus" tabindex="-1"></button>

    <main class="content">
        <?php if (!empty($flash)): ?>
            <div class="alert alert-success" role="status"><?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($lock !== null): ?>
            <div class="lesson-header">
                <div class="lesson-heading">
                    <p class="lesson-eyebrow"><?= e($sectionTitle) ?></p>
                    <h1><?= e($lesson['title']) ?></h1>
                </div>
                <span class="lesson-label"><?= icon(Course::lessonIcon($lesson)) ?><?= e($course->lessonLabel($lesson)) ?></span>
            </div>
            <div class="card quiz-gate lock-card">
                <div class="lock-art" aria-hidden="true">
                    <img class="lock-blob" src="assets/img/blob-amber.svg" alt="" width="425" height="309">
                    <span class="lock-icon"><?= icon('lock') ?></span>
                    <img class="lock-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294">
                </div>
                <div class="lock-body">
                    <h2 class="lock-title">Belum dibuka</h2>
                    <p class="alert alert-warning"><?= e($lock['message']) ?></p>
                    <ul class="lock-links">
                        <?php foreach ($lock['links'] as $m): ?>
                            <li><a class="lock-link" href="<?= e($lessonUrl($m['key'])) ?>"><span><?= e($m['title']) ?></span><?= icon('arrow') ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php elseif ($lesson['type'] === 'presentation'): ?>
            <div class="lesson-header">
                <div class="lesson-heading">
                    <p class="lesson-eyebrow"><?= e($sectionTitle) ?></p>
                    <h1><?= e($lesson['title']) ?></h1>
                </div>
                <button type="button" class="btn btn-outline btn-fullscreen" id="fullscreen-btn"><?= icon('expand') ?>Skrin Penuh</button>
            </div>

            <div class="viewer" id="viewer" data-count="<?= e($slideCount) ?>" data-current="<?= e($slide) ?>" style="--slide-progress: <?= e(round($slide * 100 / $slideCount, 2)) ?>%">
                <div class="slide-progress" aria-hidden="true"><span></span></div>
                <div class="slides">
                    <?php foreach ($lesson['slides'] as $i => $s):
                        $n = $i + 1;
                    ?>
                        <article class="slide" data-slide="<?= e($n) ?>"<?= $n === $slide ? '' : ' hidden' ?>>
                            <?php if (!empty($s['image'])): ?>
                                <img src="<?= e($s['image']) ?>" alt="<?= e($s['title'] ?? '') ?>"<?= $n === $slide ? '' : ' loading="lazy"' ?>>
                            <?php else: ?>
                                <h2><?= e($s['title'] ?? '') ?></h2>
                                <p><?= nl2br(e($s['body'] ?? '')) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="viewer-bar">
                    <div class="viewer-nav">
                        <?php if ($slide > 1): ?>
                            <a class="btn btn-nav" id="prev-btn" href="<?= e($lessonUrl($lesson['key'], $slide - 1)) ?>"><?= icon('prev') ?>SEBELUM</a>
                        <?php else: ?>
                            <a class="btn btn-nav is-disabled" id="prev-btn" aria-disabled="true"><?= icon('prev') ?>SEBELUM</a>
                        <?php endif; ?>

                        <span class="slide-counter" id="slide-counter"><?= e($slide) ?>/<?= e($slideCount) ?></span>

                        <?php if ($slide < $slideCount): ?>
                            <a class="btn btn-nav" id="next-btn" href="<?= e($lessonUrl($lesson['key'], $slide + 1)) ?>">SETERUSNYA<?= icon('next') ?></a>
                        <?php else: ?>
                            <a class="btn btn-nav is-disabled" id="next-btn" aria-disabled="true">SETERUSNYA<?= icon('next') ?></a>
                        <?php endif; ?>
                    </div>

                    <form method="post" action="lesson-complete.php" class="complete-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="lesson" value="<?= e($lesson['key']) ?>">
                        <button type="submit" class="btn btn-primary" id="complete-btn"<?= $slide === $slideCount ? '' : ' disabled' ?>>LENGKAP &amp; TERUSKAN</button>
                        <span class="complete-hint">Sampai ke slaid terakhir untuk teruskan</span>
                    </form>
                </div>
            </div>
        <?php elseif ($lesson['type'] === 'video'):
            $videoDone = isset($done[$lesson['key']]);
            $duration = (int)$lesson['duration'];
        ?>
            <div class="lesson-header">
                <div class="lesson-heading">
                    <p class="lesson-eyebrow"><?= e($sectionTitle) ?></p>
                    <h1><?= e($lesson['title']) ?></h1>
                </div>
                <span class="lesson-label"><?= icon('video') ?><?= e($course->lessonLabel($lesson)) ?></span>
            </div>

            <div class="video-lesson" id="video-lesson" data-youtube="<?= e($lesson['youtube']) ?>" data-duration="<?= e($duration) ?>"
                 data-done="<?= $videoDone ? '1' : '0' ?>" data-progress-key="<?= e(video_progress_key((int)$learner['id'], $lesson['key'])) ?>">
                <div class="video-frame">
                    <div class="video-player" id="video-player"></div>
                    <button type="button" class="video-facade" id="video-play">
                        <img class="video-facade-blob" src="assets/img/blob-orange.svg" alt="" width="487" height="354">
                        <img class="video-facade-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294">
                        <span class="video-facade-title"><?= e($lesson['title']) ?></span>
                        <span class="video-facade-play" aria-hidden="true"><?= icon('play') ?></span>
                        <span class="video-facade-cta">Main video</span>
                        <span class="video-facade-time"><?= e(format_duration($duration)) ?></span>
                    </button>
                </div>
                <div class="video-bar">
                    <div class="video-watch">
                        <p class="video-watch-text" id="video-watch-text" aria-live="polite"><?= $videoDone
                            ? 'Video ini telah ditonton sampai habis.'
                            : 'Ditonton 0:00 / ' . e(format_duration($duration)) ?></p>
                        <div class="progress-bar" aria-hidden="true"><span id="video-watch-bar" style="width: <?= $videoDone ? '100' : '0' ?>%"></span></div>
                    </div>
                    <form method="post" action="lesson-complete.php" class="complete-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="lesson" value="<?= e($lesson['key']) ?>">
                        <button type="submit" class="btn btn-primary" id="complete-btn"<?= $videoDone ? '' : ' disabled' ?>>LENGKAP &amp; TERUSKAN</button>
                        <span class="complete-hint">Tonton video sampai habis untuk teruskan</span>
                    </form>
                </div>
                <noscript><p class="alert alert-warning video-noscript">Video ini memerlukan JavaScript. Sila hidupkan JavaScript dalam pelayar anda.</p></noscript>
            </div>
            <p class="muted small video-note"><?= icon('info') ?>Video tidak boleh dilangkau ke hadapan pada tontonan pertama. Anda boleh
                menukar kelajuan sehingga 2x melalui tetapan video.</p>
        <?php else: ?>
            <div class="lesson-header">
                <div class="lesson-heading">
                    <p class="lesson-eyebrow"><?= e($sectionTitle) ?></p>
                    <h1><?= e($lesson['title']) ?></h1>
                </div>
                <span class="lesson-label"><?= icon('quiz') ?><?= e($course->lessonLabel($lesson)) ?></span>
            </div>
                <?php if ($completion !== null): ?>
                    <div class="alert alert-success">
                        Kursus ini telah selesai. <a href="certificate.php">Lihat sijil anda</a>.
                    </div>
                <?php endif; ?>

                <?php if ($attempt !== null):
                    $attemptPassed = (int)$attempt['passed'] === 1;
                    $pct = rtrim(rtrim(number_format((float)$attempt['score_percent'], 2, '.', ''), '0'), '.');
                    $isFinal = $attemptPassed && $completion === null && $allPassed;
                ?>
                    <div class="card quiz-result <?= $attemptPassed ? 'is-pass' : 'is-fail' ?><?= $isFinal ? ' is-final' : '' ?>">
                        <?php if ($attemptPassed): ?>
                            <div class="confetti" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                        <?php endif; ?>
                        <div class="result-hero">
                            <div class="score-ring" style="--score: <?= e($pct) ?>" aria-hidden="true"><span><?= e(round((float)$attempt['score_percent'])) ?>%</span></div>
                            <img class="result-mascot" src="assets/img/<?= $attemptPassed ? 'mascot-cheer.webp' : 'mascot-wave-320.webp' ?>" alt=""
                                 width="<?= $attemptPassed ? '315' : '320' ?>" height="<?= $attemptPassed ? '288' : '294' ?>">
                        </div>
                        <p class="quiz-score">Markah anda: <?= e($attempt['correct_count']) ?>/<?= e($attempt['total_questions']) ?> (<?= e($pct) ?>%)</p>
                        <p class="quiz-status"><?= $attemptPassed ? 'LULUS' : 'GAGAL' ?></p>
                        <?php if (!$attemptPassed): ?>
                            <p>Markah lulus ialah <?= e($quiz['pass_mark']) ?>%. Anda boleh mencuba lagi tanpa had.</p>
                            <a class="btn btn-primary btn-lg" href="<?= e($lessonUrl($lesson['key'])) ?>"><?= icon('refresh') ?>Cuba Lagi</a>
                        <?php elseif ($completion !== null): ?>
                            <a class="btn btn-primary btn-lg" href="certificate.php"><?= icon('award') ?>Lihat Sijil</a>
                        <?php elseif ($allPassed): ?>
                            <p>Tahniah! Tekan butang di bawah untuk menghantar penyelesaian kursus dan mendapatkan sijil anda.</p>
                            <form method="post" action="complete.php">
                                <?= csrf_field() ?>
                                <input type="hidden" name="track" value="<?= e($course->track()) ?>">
                                <button type="submit" class="btn btn-primary btn-lg btn-block">LENGKAP &amp; TERUSKAN</button>
                            </form>
                        <?php else: ?>
                            <p>Tahniah! Anda lulus kuiz ini.</p>
                            <?php if ($nextLesson !== null): ?>
                                <a class="btn btn-primary btn-lg" href="<?= e($lessonUrl($nextLesson['key'])) ?>">Teruskan ke <?= e($nextLesson['title']) ?></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <form method="post" action="quiz-submit.php" class="card quiz-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="quiz" value="<?= e($lesson['key']) ?>">
                        <p class="quiz-meta">Markah lulus: <?= e($quiz['pass_mark']) ?>% &middot; Percubaan ke-<?= e($attemptNo) ?></p>
                        <?php foreach ($quiz['questions'] as $qi => $q): ?>
                            <fieldset class="quiz-question">
                                <legend><span class="q-num"><?= e($qi + 1) ?><span class="q-dot">.</span></span> <?= e($q['text']) ?></legend>
                                <div class="quiz-options">
                                    <?php foreach ($q['options'] as $optKey => $optText): ?>
                                        <label class="quiz-option">
                                            <input type="radio" name="answers[<?= e($q['id']) ?>]" value="<?= e($optKey) ?>" required>
                                            <span><?= e($optText) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary btn-lg quiz-submit">Hantar Jawapan <?= icon('arrow') ?></button>
                    </form>
                <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
