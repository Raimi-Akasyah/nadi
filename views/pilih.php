<?php
/** @var array $learner */
/** @var array $cards list of ['course' => Course, 'percent' => int] */
/** @var array|null $completion */
/** @var string|null $flash */
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/tracks.php';
?>
<main class="choose-page">
    <section class="choose-hero">
        <img class="blob blob-tr" src="assets/img/blob-orange.svg" alt="" aria-hidden="true" width="487" height="354">
        <div class="container choose-hero-inner">
            <div class="choose-copy">
                <p class="eyebrow">Langkah seterusnya</p>
                <h1>Pilih cara belajar anda</h1>
                <p class="lead">Hai <?= e(learner_name($learner)) ?>! Kedua-dua cara menggunakan soalan kuiz yang sama dan
                    memberi sijil yang sama. Selesaikan salah satu untuk mendapatkan sijil anda.</p>
            </div>
            <img class="choose-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294">
        </div>
    </section>

    <div class="container choose-body">
        <?php if (!empty($flash)): ?>
            <div class="alert alert-success" role="status"><?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($completion !== null): ?>
            <div class="alert alert-success">Kursus ini telah selesai. <a href="certificate.php">Lihat sijil anda</a>.
                Anda masih boleh membuka kedua-dua cara belajar.</div>
        <?php endif; ?>

        <ul class="track-grid">
            <?php foreach ($cards as $card):
                /** @var Course $c */
                $c = $card['course'];
                $info = track_info($c);
                $pct = (int)$card['percent'];
            ?>
                <li class="track-card track-<?= e($c->track()) ?>" data-track="<?= e($c->track()) ?>">
                    <div class="track-head">
                        <span class="track-icon" aria-hidden="true"><?= icon($info['icon']) ?></span>
                        <div>
                            <p class="track-tagline"><?= e($info['tagline']) ?></p>
                            <h2 class="track-title"><?= e($c->trackTitle()) ?></h2>
                        </div>
                    </div>
                    <p class="track-text"><?= e($info['text']) ?></p>
                    <ul class="chip-list">
                        <?php foreach ($info['facts'] as $fact): ?>
                            <li class="chip"><?= e($fact) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="progress<?= $pct >= 100 ? ' is-complete' : '' ?>">
                        <div class="progress-text"><?= e($pct) ?>% selesai</div>
                        <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e($pct) ?>"
                             aria-label="Kemajuan <?= e($c->trackTitle()) ?>">
                            <span style="width: <?= e($pct) ?>%"></span>
                        </div>
                    </div>
                    <a class="btn btn-primary btn-lg btn-block track-cta" href="course.php?<?= e(http_build_query(['track' => $c->track()])) ?>">
                        <?= $pct > 0 ? 'Teruskan' : 'Mula' ?> <?= e($info['cta']) ?> <?= icon('arrow') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="choose-foot">
            <p class="muted small"><?= icon('info') ?>Anda boleh menukar cara belajar bila-bila masa. Kemajuan setiap cara disimpan.</p>
            <form method="post" action="reset.php" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline">Selesai / Keluar</button>
            </form>
        </div>
    </div>
</main>
