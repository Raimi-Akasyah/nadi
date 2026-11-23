<?php
/** @var Course $course */
/** @var string $code */
/** @var bool $searched */
/** @var array|null $found */
?>
<main class="container narrow">
    <div class="card">
        <h1>Pengesahan Sijil</h1>
        <p class="muted">Masukkan nombor sijil (contoh: NADI-1A2B3C4D5E) untuk menyemak kesahihannya.</p>
        <form method="get" action="verify.php" class="form">
            <div class="field">
                <label for="f-code">No. Sijil</label>
                <input id="f-code" name="code" type="text" maxlength="32" value="<?= e($code) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Semak</button>
        </form>

        <?php if ($searched): ?>
            <?php if ($found !== null): ?>
                <div class="alert alert-success verify-result" role="status">
                    <p><strong>Sijil sah</strong></p>
                    <p>Nama: <?= e($found['first_name'] . ' ' . $found['last_name']) ?><br>
                        Kursus: <?= e($course->title()) ?><br>
                        Tarikh: <?= e(format_date_ms($found['completed_at'])) ?><br>
                        No. Sijil: <?= e($found['certificate_code']) ?></p>
                </div>
            <?php else: ?>
                <div class="alert alert-error verify-result" role="status">Sijil tidak dijumpai.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
