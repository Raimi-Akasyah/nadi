<?php
/** @var string $login */
/** @var string|null $error */
/** @var bool $notFound */
/** @var bool $remember */
$resumeLogin = $login;
$resumeRemember = $remember;
?>
<main class="auth-shell">
    <div class="auth-grid">
        <?php require __DIR__ . '/auth-brand.php'; ?>
        <div class="card auth-card">
            <h1>Sambung Semula</h1>
            <p class="muted">Sudah mendaftar sebelum ini, di peranti ini atau peranti lain? Masukkan nama penuh atau e-mel anda
                untuk menyambung kemajuan, kuiz dan sijil anda.</p>
            <?php if ($notFound): ?>
                <div class="alert alert-warning" role="alert">
                    <p>Tiada peserta berdaftar dengan nama atau e-mel ini. Semak ejaan anda (nama mesti sama seperti semasa
                        mendaftar), cuba dengan e-mel, atau daftar sebagai peserta baharu.</p>
                    <p><a href="register.php">Daftar Sekarang</a></p>
                </div>
            <?php elseif ($error !== null): ?>
                <div class="alert alert-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>
            <?php require __DIR__ . '/resume-form.php'; ?>
            <p class="muted small auth-note">Di komputer awam, tekan "Selesai / Keluar" sebelum meninggalkan komputer.
                Anda juga akan dikeluarkan selepas <?= e(LEARNER_IDLE_MINUTES) ?> minit tanpa aktiviti.</p>
            <p class="small">Belum mendaftar? <a href="register.php">Daftar sebagai peserta baharu</a>.</p>
        </div>
    </div>
</main>
