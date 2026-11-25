<?php
/** "Sambung Semula" form, included by the landing, registration and resume views. */
/** @var string $resumeEmail */
/** @var bool $resumeRemember */
require_once __DIR__ . '/icons.php';
$resumeEmail = $resumeEmail ?? '';
$resumeRemember = $resumeRemember ?? false;
?>
<form method="post" action="resume.php" class="form resume-form">
    <?= csrf_field() ?>
    <div class="field">
        <label for="r-email">E-mel yang anda gunakan semasa mendaftar</label>
        <input id="r-email" name="email" type="email" maxlength="190" autocomplete="email" required
               value="<?= e($resumeEmail) ?>">
    </div>
    <div class="field field-check">
        <label for="r-remember">
            <input id="r-remember" name="remember" type="checkbox" value="1"<?= $resumeRemember ? ' checked' : '' ?>>
            Ingat saya pada peranti ini selama 30 hari
        </label>
        <p class="muted small field-help"><?= icon('shield') ?>Tandakan hanya pada telefon atau komputer anda sendiri.</p>
    </div>
    <button type="submit" class="btn btn-primary btn-lg">Sambung Semula <?= icon('arrow') ?></button>
</form>
