<?php
/** @var Course $course */
/** @var array|null $learner */
/** @var bool $signedOut */
require_once __DIR__ . '/icons.php';

$moduleCount = 0;
foreach ($course->sections() as $section) {
    if (strncmp($section['key'], 'modul', 5) === 0) {
        $moduleCount++;
    }
}
$quizCount = count($course->quizLessons());
$initials = $learner !== null
    ? mb_strtoupper(mb_substr(trim($learner['first_name']), 0, 1) . mb_substr(trim($learner['last_name']), 0, 1))
    : '';
$steps = [
    ['user', 'Daftar', 'Isi maklumat asas anda. Tiada akaun atau kata laluan.'],
    ['book', 'Belajar', 'Ikuti slaid setiap modul mengikut kadar anda sendiri, di telefon atau komputer.'],
    ['clipboard', 'Kuiz', 'Lulus kuiz modul untuk membuka modul yang seterusnya.'],
    ['award', 'Sijil', 'Muat turun sijil penyelesaian (PDF) dengan nama anda.'],
];
?>
<main class="landing">
    <section class="hero">
        <img class="blob blob-tr" src="assets/img/blob-orange.svg" alt="" aria-hidden="true" width="487" height="354">
        <img class="blob blob-bl" src="assets/img/blob-amber.svg" alt="" aria-hidden="true" width="425" height="309">
        <div class="container hero-grid">
            <div class="hero-copy">
                <p class="eyebrow">Kursus Dalam Talian NADI</p>
                <h1><?= e($course->title()) ?></h1>
                <span class="dots" aria-hidden="true"></span>
                <p class="lead">Pelajari asas kecerdasan buatan (AI) secara percuma mengikut kadar anda sendiri.
                    Lengkapkan kelima-lima modul dan lulus kuiz di hujung setiap modul dengan markah sekurang-kurangnya
                    <?= e($course->passMark()) ?>% untuk muat turun sijil penyelesaian anda.</p>
                <?php if ($learner !== null): ?>
                    <div class="greeting-card">
                        <span class="avatar" aria-hidden="true"><?= e($initials) ?></span>
                        <p>Selamat kembali, <strong><?= e($learner['first_name'] . ' ' . $learner['last_name']) ?></strong>.</p>
                    </div>
                    <div class="hero-actions">
                        <a class="btn btn-primary btn-lg" href="course.php">Teruskan Kursus <?= icon('arrow') ?></a>
                        <form method="post" action="reset.php" class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-link">Bukan anda? Mula semula</button>
                        </form>
                    </div>
                <?php else: ?>
                    <?php if (!empty($signedOut)): ?>
                        <div class="alert alert-success" role="status">Anda telah keluar. Maklumat anda tidak lagi dipaparkan pada peranti ini.</div>
                    <?php endif; ?>
                    <div class="hero-actions">
                        <a class="btn btn-primary btn-lg" href="register.php">Daftar Sekarang <?= icon('arrow') ?></a>
                    </div>
                    <p class="hero-note">Tiada akaun atau kata laluan diperlukan. Hanya isi maklumat asas anda.
                        Sudah mendaftar? <a class="hero-link" href="#sambung-semula">Sambung Semula</a> dengan e-mel anda.</p>
                <?php endif; ?>
                <ul class="stat-strip">
                    <li><span class="stat-num"><?= e($moduleCount) ?></span> <span class="stat-label">modul</span></li>
                    <li><span class="stat-num"><?= e($quizCount) ?></span> <span class="stat-label">kuiz</span></li>
                    <li><span class="stat-num"><?= e($course->passMark()) ?>%</span> <span class="stat-label">markah lulus</span></li>
                    <li><span class="stat-num stat-icon"><?= icon('award') ?></span> <span class="stat-label">sijil percuma</span></li>
                </ul>
            </div>
            <div class="hero-art">
                <div class="hero-mascot-wrap">
                    <img class="hero-mascot"
                         src="assets/img/mascot-wave-640.webp"
                         srcset="assets/img/mascot-wave-320.webp 320w, assets/img/mascot-wave-640.webp 640w"
                         sizes="(min-width: 900px) 360px, 220px"
                         width="640" height="589" alt="Maskot harimau robot NADI">
                </div>
                <?php require __DIR__ . '/partners.php'; ?>
            </div>
        </div>
    </section>

    <section class="section steps-section" aria-labelledby="cara-heading">
        <div class="container">
            <div class="section-head">
                <h2 id="cara-heading">Cara ia berfungsi</h2>
                <span class="dots" aria-hidden="true"></span>
            </div>
            <ol class="steps">
                <?php foreach ($steps as $i => [$stepIcon, $stepTitle, $stepText]): ?>
                    <li class="step">
                        <span class="step-icon"><?= icon($stepIcon) ?><span class="step-num"><?= e($i + 1) ?></span></span>
                        <h3><?= e($stepTitle) ?></h3>
                        <p><?= e($stepText) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <?php if ($learner === null): ?>
        <section class="section" id="sambung-semula">
            <div class="container">
                <div class="card resume-card">
                    <div class="resume-copy">
                        <span class="resume-icon"><?= icon('refresh') ?></span>
                        <h2>Sambung Semula</h2>
                        <p class="muted">Sudah mendaftar sebelum ini, di peranti ini atau peranti lain? Masukkan e-mel anda
                            untuk menyambung kemajuan, kuiz dan sijil anda. Tiada kata laluan diperlukan.</p>
                    </div>
                    <div class="resume-body">
                        <?php require __DIR__ . '/resume-form.php'; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="section modules-section" aria-labelledby="kandungan-heading">
        <div class="container">
            <div class="section-head">
                <h2 id="kandungan-heading">Kandungan Kursus</h2>
                <span class="dots" aria-hidden="true"></span>
            </div>
            <ol class="module-grid">
                <?php foreach ($course->sections() as $section):
                    $isModule = strncmp($section['key'], 'modul-', 6) === 0;
                    $isIntro = $section['key'] === 'pengenalan';
                ?>
                    <li class="module-card<?= $isModule ? '' : ($isIntro ? ' is-intro' : ' is-outro') ?>">
                        <span class="module-badge" aria-hidden="true"><?php
                            if ($isModule) {
                                echo e(substr($section['key'], 6));
                            } elseif ($isIntro) {
                                echo 'Mula';
                            } else {
                                echo icon('flag');
                            }
                        ?></span>
                        <div class="module-body">
                            <h3 class="module-title"><?= e($section['title']) ?></h3>
                            <ul class="chip-list">
                                <?php foreach ($section['lessons'] as $lesson): ?>
                                    <li class="chip<?= $lesson['type'] === 'quiz' ? ' chip-quiz' : '' ?>"><?= icon($lesson['type'] === 'quiz' ? 'quiz' : 'slides') ?><?= e($course->lessonLabel($lesson)) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>
</main>
