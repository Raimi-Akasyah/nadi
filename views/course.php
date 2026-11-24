<?php
/** @var Course $course */
/** @var array $learner */
/** @var array $lesson */
/** @var array $done */
/** @var int $slide */
/** @var int $slideCount */
/** @var int $percent */
/** @var array $missing */
/** @var array|null $quiz */
/** @var array|null $attempt */
/** @var int $attemptNo */
/** @var array|null $completion */
/** @var string|null $flash */

$lessonUrl = static function (string $key, ?int $slideNo = null): string {
    $q = ['lesson' => $key];
    if ($slideNo !== null) {
        $q['slide'] = $slideNo;
    }
    return 'course.php?' . http_build_query($q);
};
?>
<header class="topbar">
    <a class="topbar-home" href="index.php">&larr; Pergi ke Laman Utama</a>
    <a class="topbar-logo" href="index.php"><img src="assets/img/logo.svg" alt="NADI" height="32"></a>
    <div class="topbar-right">
        <span class="topbar-user"><?= e($learner['first_name'] . ' ' . $learner['last_name']) ?></span>
        <form method="post" action="reset.php" class="inline-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline btn-sm">Selesai / Keluar</button>
        </form>
    </div>
</header>

<div class="course-layout">
    <aside class="sidebar">
        <h2 class="sidebar-title"><?= e($course->title()) ?></h2>
        <div class="progress">
            <div class="progress-text"><?= e($percent) ?>% selesai</div>
            <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e($percent) ?>">
                <span style="width: <?= e($percent) ?>%"></span>
            </div>
        </div>
        <input type="search" id="lesson-search" class="sidebar-search" placeholder="Cari mengikut tajuk pelajaran"
               aria-label="Cari mengikut tajuk pelajaran">

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
            ?>
                <details class="sidebar-section" data-section="<?= e($section['key']) ?>"<?= $isCurrentSection ? ' open' : '' ?>>
                    <summary>
                        <span class="section-title"><?= e($section['title']) ?></span>
                        <span class="section-count"><?= e($sectionDone) ?>/<?= e(count($sectionLessons)) ?></span>
                    </summary>
                    <ul class="lesson-list">
                        <?php foreach ($sectionLessons as $l):
                            $isDone = isset($done[$l['key']]);
                            $isCurrent = $l['key'] === $lesson['key'];
                        ?>
                            <li class="lesson-row<?= $isCurrent ? ' is-current' : '' ?><?= $isDone ? ' is-done' : '' ?>"
                                data-title="<?= e($l['title']) ?>">
                                <a href="<?= e($lessonUrl($l['key'])) ?>"<?= $isCurrent ? ' aria-current="page"' : '' ?>>
                                    <span class="lesson-tick" aria-hidden="true"><?= $isDone ? '&#10003;' : '' ?></span>
                                    <span class="lesson-meta">
                                        <span class="lesson-title"><?= e($l['title']) ?></span>
                                        <span class="lesson-label"><?= e($course->lessonLabel($l)) ?></span>
                                    </span>
                                    <?php if ($isDone): ?><span class="sr-only">(selesai)</span><?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endforeach; ?>
        </nav>
    </aside>

    <main class="content">
        <?php if (!empty($flash)): ?>
            <div class="alert alert-success" role="status"><?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($lesson['type'] === 'presentation'): ?>
            <div class="lesson-header">
                <h1><?= e($lesson['title']) ?></h1>
                <button type="button" class="btn btn-outline" id="fullscreen-btn">Skrin Penuh</button>
            </div>

            <div class="viewer" id="viewer" data-count="<?= e($slideCount) ?>" data-current="<?= e($slide) ?>">
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
                    <?php if ($slide > 1): ?>
                        <a class="btn btn-nav" id="prev-btn" href="<?= e($lessonUrl($lesson['key'], $slide - 1)) ?>">&lsaquo; SEBELUM</a>
                    <?php else: ?>
                        <a class="btn btn-nav is-disabled" id="prev-btn" aria-disabled="true">&lsaquo; SEBELUM</a>
                    <?php endif; ?>

                    <span class="slide-counter" id="slide-counter"><?= e($slide) ?>/<?= e($slideCount) ?></span>

                    <?php if ($slide < $slideCount): ?>
                        <a class="btn btn-nav" id="next-btn" href="<?= e($lessonUrl($lesson['key'], $slide + 1)) ?>">SETERUSNYA &rsaquo;</a>
                    <?php else: ?>
                        <a class="btn btn-nav is-disabled" id="next-btn" aria-disabled="true">SETERUSNYA &rsaquo;</a>
                    <?php endif; ?>

                    <form method="post" action="lesson-complete.php" class="complete-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="lesson" value="<?= e($lesson['key']) ?>">
                        <button type="submit" class="btn btn-primary" id="complete-btn"<?= $slide === $slideCount ? '' : ' disabled' ?>>LENGKAP &amp; TERUSKAN</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="lesson-header">
                <h1><?= e($lesson['title']) ?></h1>
                <span class="lesson-label"><?= e($course->lessonLabel($lesson)) ?></span>
            </div>
            <?php if (!empty($missing)): ?>
                <div class="card quiz-gate">
                    <p class="alert alert-warning">Sila lengkapkan semua modul (Modul 1 hingga Modul 5) sebelum menjawab Kuiz Akhir.</p>
                    <p>Pelajaran yang belum selesai:</p>
                    <ul>
                        <?php foreach ($missing as $m): ?>
                            <li><a href="<?= e($lessonUrl($m['key'])) ?>"><?= e($m['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <?php if ($completion !== null): ?>
                    <div class="alert alert-success">
                        Kursus ini telah selesai. <a href="certificate.php">Lihat sijil anda</a>.
                    </div>
                <?php endif; ?>

                <?php if ($attempt !== null):
                    $passed = (int)$attempt['passed'] === 1;
                    $pct = rtrim(rtrim(number_format((float)$attempt['score_percent'], 2, '.', ''), '0'), '.');
                ?>
                    <div class="card quiz-result <?= $passed ? 'is-pass' : 'is-fail' ?>">
                        <p class="quiz-score">Markah anda: <?= e($attempt['correct_count']) ?>/<?= e($attempt['total_questions']) ?> (<?= e($pct) ?>%)</p>
                        <p class="quiz-status"><?= $passed ? 'LULUS' : 'GAGAL' ?></p>
                        <?php if (!$passed): ?>
                            <p>Markah lulus ialah <?= e($quiz['pass_mark']) ?>%. Anda boleh mencuba lagi tanpa had.</p>
                            <a class="btn btn-primary" href="<?= e($lessonUrl($lesson['key'])) ?>">Cuba Lagi</a>
                        <?php elseif ($completion === null): ?>
                            <p>Tahniah! Tekan butang di bawah untuk menghantar penyelesaian kursus dan mendapatkan sijil anda.</p>
                            <form method="post" action="complete.php">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary">LENGKAP &amp; TERUSKAN</button>
                            </form>
                        <?php else: ?>
                            <a class="btn btn-primary" href="certificate.php">Lihat Sijil</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <form method="post" action="quiz-submit.php" class="card quiz-form">
                        <?= csrf_field() ?>
                        <p class="muted">Markah lulus: <?= e($quiz['pass_mark']) ?>% &middot; Percubaan ke-<?= e($attemptNo) ?></p>
                        <?php foreach ($quiz['questions'] as $qi => $q): ?>
                            <fieldset class="quiz-question">
                                <legend><?= e($qi + 1) ?>. <?= e($q['text']) ?></legend>
                                <?php foreach ($q['options'] as $optKey => $optText): ?>
                                    <label class="quiz-option">
                                        <input type="radio" name="answers[<?= e($q['id']) ?>]" value="<?= e($optKey) ?>" required>
                                        <span><?= e($optText) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary">Hantar Jawapan</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
