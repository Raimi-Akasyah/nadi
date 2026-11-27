<?php
/** @var array $old */
/** @var array $errors */
/** @var bool $remember */
/** @var string|null $formMessage */
require_once __DIR__ . '/icons.php';
$choices = registration_choices();
$val = static function (string $k) use ($old): string {
    return (string)($old[$k] ?? '');
};
// The error under a field, and the attributes that tie it (and any help text) to the input.
$err = static function (string $k) use ($errors): string {
    return isset($errors[$k]) ? '<p class="field-error" id="e-' . e($k) . '">' . icon('alert') . e($errors[$k]) . '</p>' : '';
};
$aria = static function (string $k, string $help = '') use ($errors): string {
    $ids = array_filter([$help, isset($errors[$k]) ? 'e-' . $k : '']);
    return ($ids ? ' aria-describedby="' . e(implode(' ', $ids)) . '"' : '') . (isset($errors[$k]) ? ' aria-invalid="true"' : '');
};
$cls = static function (string $k, string $extra = '') use ($errors): string {
    return 'field' . ($extra !== '' ? ' ' . $extra : '') . (isset($errors[$k]) ? ' has-error' : '');
};
$sel = static function (string $a, string $b): string {
    return $a === $b ? ' selected' : '';
};
$centers = nadi_centers();
$occupationOther = $val('occupation') === REG_OTHER;
$nadiOther = $val('nadi') === REG_OTHER;
?>
<main class="auth-shell">
    <div class="auth-grid">
        <?php require __DIR__ . '/auth-brand.php'; ?>
        <div class="card auth-card">
            <h1>Pendaftaran Peserta</h1>
            <p class="muted">Isi maklumat di bawah untuk mula belajar. Nama anda akan dicetak pada sijil seperti yang ditulis di sini.</p>
            <div class="alert alert-info">
                Sudah mendaftar sebelum ini, di peranti ini atau peranti lain?
                <a href="resume.php">Sambung Semula dengan nama penuh atau e-mel anda</a>. Jika anda mendaftar semula dengan e-mel yang sama,
                kemajuan anda yang sedia ada akan disambung semula.
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert">Sila betulkan ralat di bawah.</div>
            <?php elseif (!empty($formMessage)): ?>
                <div class="alert alert-error" role="alert"><?= e($formMessage) ?></div>
            <?php endif; ?>

            <form method="post" action="register.php" class="form" id="register-form">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="<?= e($cls('full_name')) ?>">
                        <label for="f-full_name">Nama Penuh <span class="req">*</span></label>
                        <input id="f-full_name" name="full_name" type="text" value="<?= e($val('full_name')) ?>" required
                               maxlength="<?= e(FULL_NAME_MAX) ?>" autocomplete="name" autocapitalize="words"<?= $aria('full_name', 'h-full_name') ?>>
                        <p class="muted small field-note" id="h-full_name">Tulis nama penuh seperti dalam kad pengenalan. Nama ini dicetak pada sijil dan boleh digunakan untuk Sambung Semula.</p>
                        <?= $err('full_name') ?>
                    </div>

                    <div class="<?= e($cls('age', 'field-age')) ?>">
                        <label for="f-age">Umur <span class="req">*</span></label>
                        <input id="f-age" name="age" type="number" value="<?= e($val('age')) ?>" required min="5" max="120" step="1" inputmode="numeric"<?= $aria('age') ?>>
                        <?= $err('age') ?>
                    </div>
                    <fieldset class="<?= e($cls('gender', 'field-radios')) ?>"<?= $aria('gender') ?>>
                        <legend>Jantina <span class="req">*</span></legend>
                        <div class="radio-row">
                            <?php foreach ($choices['genders'] as $i => $g): ?>
                                <label class="radio-pill" for="f-gender-<?= e($i) ?>">
                                    <input id="f-gender-<?= e($i) ?>" type="radio" name="gender" value="<?= e($g) ?>" required<?= $val('gender') === $g ? ' checked' : '' ?>>
                                    <span><?= e($g) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?= $err('gender') ?>
                    </fieldset>

                    <div class="<?= e($cls('email')) ?>">
                        <label for="f-email">E-mel <span class="req">*</span></label>
                        <input id="f-email" name="email" type="email" value="<?= e($val('email')) ?>" required maxlength="190" autocomplete="email"<?= $aria('email') ?>>
                        <?= $err('email') ?>
                    </div>

                    <div class="<?= e($cls('occupation')) ?>">
                        <label for="f-occupation">Pekerjaan <span class="req">*</span></label>
                        <select id="f-occupation" name="occupation" required data-other="f-occupation_other-field"<?= $aria('occupation') ?>>
                            <option value="">Pilih pekerjaan</option>
                            <?php foreach ($choices['occupations'] as $o): ?>
                                <option value="<?= e($o) ?>"<?= $sel($val('occupation'), $o) ?>><?= e($o) ?></option>
                            <?php endforeach; ?>
                            <option value="<?= e(REG_OTHER) ?>"<?= $sel($val('occupation'), REG_OTHER) ?>><?= e(REG_OTHER_LABEL) ?></option>
                        </select>
                        <?= $err('occupation') ?>
                    </div>
                    <div class="<?= e($cls('occupation_other', 'field-other')) ?>" id="f-occupation_other-field" data-other-field>
                        <label for="f-occupation_other">Nyatakan pekerjaan anda<span class="muted"> (jika Lain-lain)</span></label>
                        <input id="f-occupation_other" name="occupation_other" type="text" value="<?= e($val('occupation_other')) ?>" maxlength="100"<?= $occupationOther ? ' required' : '' ?><?= $aria('occupation_other') ?>>
                        <?= $err('occupation_other') ?>
                    </div>
                    <div class="<?= e($cls('state', 'field-half')) ?>">
                        <label for="f-state">Negeri <span class="req">*</span></label>
                        <select id="f-state" name="state" required<?= $aria('state') ?>>
                            <option value="">Pilih negeri</option>
                            <?php foreach (array_keys($centers) as $s): ?>
                                <option value="<?= e($s) ?>"<?= $sel($val('state'), $s) ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('state') ?>
                    </div>
                    <div class="<?= e($cls('district', 'field-half')) ?>">
                        <label for="f-district">Daerah <span class="req">*</span></label>
                        <select id="f-district" name="district" required<?= $aria('district') ?>>
                            <option value="">Pilih daerah</option>
                            <?php foreach ($centers as $s => $districts): ?>
                                <optgroup label="<?= e($s) ?>" data-state="<?= e($s) ?>">
                                    <?php foreach (array_keys($districts) as $d): ?>
                                        <option value="<?= e($d) ?>"<?= $val('state') === $s ? $sel($val('district'), $d) : '' ?>><?= e($d) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('district') ?>
                    </div>
                    <div class="<?= e($cls('nadi')) ?>">
                        <label for="f-nadi">Pusat NADI <span class="req">*</span></label>
                        <select id="f-nadi" name="nadi" required data-other="f-nadi_other-field"<?= $aria('nadi', 'h-nadi') ?>>
                            <option value="">Pilih pusat NADI</option>
                            <?php foreach ($centers as $s => $districts): foreach ($districts as $d => $list): ?>
                                <optgroup label="<?= e($d . ', ' . $s) ?>" data-state="<?= e($s) ?>" data-district="<?= e($d) ?>">
                                    <?php foreach ($list as $code => $name): ?>
                                        <option value="<?= e($code) ?>"<?= $sel($val('nadi'), (string)$code) ?>><?= e($name) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; endforeach; ?>
                            <option value="<?= e(REG_OTHER) ?>"<?= $sel($val('nadi'), REG_OTHER) ?>><?= e(REG_OTHER_LABEL) ?> (tiada dalam senarai)</option>
                        </select>
                        <p class="muted small field-note" id="h-nadi">Pusat NADI tempat anda menyertai kursus ini. Pilih negeri dan daerah dahulu.</p>
                        <?= $err('nadi') ?>
                    </div>
                    <div class="<?= e($cls('nadi_other', 'field-other')) ?>" id="f-nadi_other-field" data-other-field>
                        <label for="f-nadi_other">Nama pusat NADI anda<span class="muted"> (jika Lain-lain)</span></label>
                        <input id="f-nadi_other" name="nadi_other" type="text" value="<?= e($val('nadi_other')) ?>" maxlength="<?= e(REG_OTHER_MAX) ?>"
                               placeholder="Contoh: NADI Kampung Baru"<?= $nadiOther ? ' required' : '' ?><?= $aria('nadi_other') ?>>
                        <?= $err('nadi_other') ?>
                    </div>
                </div>

                <div class="field field-check">
                    <label for="f-remember">
                        <input id="f-remember" name="remember" type="checkbox" value="1"<?= !empty($remember) ? ' checked' : '' ?>>
                        Ingat saya pada peranti ini selama 30 hari
                    </label>
                    <p class="muted small field-help"><?= icon('shield') ?>Tandakan hanya jika ini telefon atau komputer anda sendiri.
                        Di komputer awam atau dikongsi (contohnya di pusat komuniti atau kafe), biarkan kosong:
                        tekan "Selesai / Keluar" sebelum meninggalkan komputer. Menutup pelayar sahaja tidak
                        semestinya mengeluarkan anda. Anda juga akan dikeluarkan selepas 30 minit tanpa aktiviti.
                        Kemajuan anda tetap disimpan dan boleh disambung semula dengan nama penuh atau e-mel anda.</p>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Hantar &amp; Mula Kursus <?= icon('arrow') ?></button>
            </form>
        </div>
    </div>
</main>
