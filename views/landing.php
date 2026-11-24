<?php
/** @var Course $course */
/** @var array|null $learner */
/** @var bool $signedOut */
?>
<main>
    <section class="hero">
        <div class="container">
            <p class="eyebrow">Kursus Dalam Talian NADI</p>
            <h1><?= e($course->title()) ?></h1>
            <p class="lead">Pelajari asas kecerdasan buatan (AI) secara percuma mengikut kadar anda sendiri.
                Lengkapkan kelima-lima modul, lulus Kuiz Akhir dengan markah sekurang-kurangnya
                <?= e($course->quiz()['pass_mark']) ?>%, dan muat turun sijil penyelesaian anda serta-merta.</p>
            <?php if ($learner !== null): ?>
                <p>Selamat kembali, <strong><?= e($learner['first_name'] . ' ' . $learner['last_name']) ?></strong>.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="course.php">Teruskan Kursus</a>
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
                    <a class="btn btn-primary btn-lg" href="register.php">Daftar Sekarang</a>
                </div>
                <p class="muted small">Tiada akaun atau kata laluan diperlukan. Hanya isi maklumat asas anda.
                    Sudah mendaftar? <a class="hero-link" href="#sambung-semula">Sambung Semula</a> dengan e-mel anda.</p>
            <?php endif; ?>
        </div>
    </section>
    <?php if ($learner === null): ?>
        <section class="container" id="sambung-semula">
            <div class="card resume-card">
                <h2>Sambung Semula</h2>
                <p class="muted">Sudah mendaftar sebelum ini, di peranti ini atau peranti lain? Masukkan e-mel anda
                    untuk menyambung kemajuan, kuiz dan sijil anda. Tiada kata laluan diperlukan.</p>
                <?php require __DIR__ . '/resume-form.php'; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="container">
        <h2>Kandungan Kursus</h2>
        <ol class="section-list">
            <?php foreach ($course->sections() as $section): ?>
                <li>
                    <strong><?= e($section['title']) ?></strong>
                    <span class="muted small">
                        <?php foreach ($section['lessons'] as $i => $lesson): ?>
                            <?= $i > 0 ? ' &middot; ' : '' ?><?= e($course->lessonLabel($lesson)) ?>
                        <?php endforeach; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
</main>
