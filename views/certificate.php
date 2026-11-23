<?php
/** @var array $learner */
/** @var Course $course */
/** @var array $completion */
$verifyUrl = 'verify.php?' . http_build_query(['code' => $completion['certificate_code']]);
?>
<main class="container narrow">
    <div class="card certificate-card">
        <h1>Tahniah!</h1>
        <p>Anda telah berjaya menamatkan kursus ini. Sijil anda sudah sedia untuk dimuat turun.</p>
        <dl class="certificate-details">
            <dt>Nama</dt>
            <dd><?= e($learner['first_name'] . ' ' . $learner['last_name']) ?></dd>
            <dt>Kursus</dt>
            <dd><?= e($course->title()) ?></dd>
            <dt>Tarikh</dt>
            <dd><?= e(format_date_ms($completion['completed_at'])) ?></dd>
            <dt>No. Sijil</dt>
            <dd><?= e($completion['certificate_code']) ?></dd>
        </dl>
        <p><a class="btn btn-primary btn-lg" href="certificate-download.php">Muat Turun Sijil (PDF)</a></p>
        <p class="muted small">Sesiapa boleh menyemak kesahihan sijil ini di
            <a href="<?= e($verifyUrl) ?>">halaman pengesahan sijil</a>.</p>
        <p><a href="course.php">Kembali ke kursus</a></p>
    </div>
</main>
