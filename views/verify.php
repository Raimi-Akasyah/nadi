<?php
/** @var Course $course */
/** @var string $code */
/** @var bool $searched */
/** @var array|null $found */
require_once __DIR__ . '/icons.php';
?>
<main class="container narrow page-pad">
    <div class="card verify-card">
        <div class="verify-head">
            <span class="verify-shield"><?= icon('shield') ?></span>
            <div>
                <h1>Pengesahan Sijil</h1>
                <p class="muted">Masukkan nombor sijil (contoh: NADI-1A2B3C4D5E) untuk menyemak kesahihannya.</p>
            </div>
        </div>
        <form method="get" action="verify.php" class="form verify-form">
            <div class="field">
                <label for="f-code">No. Sijil</label>
                <input id="f-code" name="code" type="text" maxlength="32" value="<?= e($code) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg"><?= icon('search') ?>Semak</button>
        </form>

        <?php if ($searched): ?>
            <?php if ($found !== null): ?>
                <div class="alert alert-success verify-result" role="status">
                    <p><strong>Sijil sah</strong></p>
                    <dl class="verify-details">
                        <dt>Nama</dt><dd><?= e(learner_name($found)) ?></dd>
                        <dt>Kursus</dt><dd><?= e($course->title()) ?></dd>
                        <dt>Tarikh</dt><dd><?= e(format_date_ms($found['completed_at'])) ?></dd>
                        <dt>No. Sijil</dt><dd><span class="cert-code"><?= e($found['certificate_code']) ?></span></dd>
                    </dl>
                </div>
            <?php else: ?>
                <div class="alert alert-error verify-result" role="status">Sijil tidak dijumpai.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
