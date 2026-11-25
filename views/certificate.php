<?php
/** @var array $learner */
/** @var Course $course */
/** @var array $completion */
require_once __DIR__ . '/icons.php';
$verifyUrl = 'verify.php?' . http_build_query(['code' => $completion['certificate_code']]);
?>
<main class="cert-page">
    <section class="celebrate">
        <img class="blob blob-tr" src="assets/img/blob-orange.svg" alt="" aria-hidden="true" width="487" height="354">
        <img class="blob blob-bl" src="assets/img/blob-amber.svg" alt="" aria-hidden="true" width="425" height="309">
        <div class="confetti" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
        <div class="container celebrate-inner">
            <img class="celebrate-mascot" src="assets/img/mascot-cheer.webp" alt="" width="315" height="288">
            <div class="celebrate-copy">
                <p class="eyebrow">Kursus selesai</p>
                <h1>Tahniah!</h1>
                <p class="lead">Anda telah berjaya menamatkan kursus ini. Sijil anda sudah sedia untuk dimuat turun.</p>
            </div>
        </div>
    </section>
    <div class="container cert-container">
        <div class="card certificate-card">
            <div class="ticket">
                <p class="ticket-kicker"><?= icon('award') ?>Sijil Penyelesaian</p>
                <dl class="certificate-details">
                    <dt>Nama</dt>
                    <dd><?= e($learner['first_name'] . ' ' . $learner['last_name']) ?></dd>
                    <dt>Kursus</dt>
                    <dd><?= e($course->title()) ?></dd>
                    <dt>Tarikh</dt>
                    <dd><?= e(format_date_ms($completion['completed_at'])) ?></dd>
                    <dt>No. Sijil</dt>
                    <dd><span class="cert-code"><?= e($completion['certificate_code']) ?></span></dd>
                </dl>
            </div>
            <div class="certificate-actions">
                <a class="btn btn-primary btn-lg" href="certificate-download.php"><?= icon('download') ?>Muat Turun Sijil (PDF)</a>
                <form method="post" action="reset.php" class="inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-lg">Selesai / Keluar</button>
                </form>
            </div>
            <p class="alert alert-info">Selepas memuat turun sijil, tekan "Selesai / Keluar" supaya nama, kemajuan dan sijil anda
                tidak dipaparkan kepada pengguna seterusnya pada peranti ini. Di komputer awam, simpan fail sijil
                ke telefon atau e-mel anda dan padamkan salinan daripada komputer tersebut.</p>
            <p class="muted small"><?= icon('shield') ?>Sesiapa boleh menyemak kesahihan sijil ini di
                <a href="<?= e($verifyUrl) ?>">halaman pengesahan sijil</a>.</p>
            <p><a class="back-link" href="course.php"><?= icon('back') ?>Kembali ke kursus</a></p>
        </div>
    </div>
</main>
