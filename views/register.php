<?php
/** @var array $old */
/** @var array $errors */
/** @var bool $remember */
/** @var string|null $formMessage */
$fields = [
    'first_name' => ['Nama Pertama', 'text', 'maxlength="100" autocomplete="given-name"'],
    'last_name'  => ['Nama Akhir', 'text', 'maxlength="100" autocomplete="family-name"'],
    'age'        => ['Umur', 'number', 'min="5" max="120" step="1" inputmode="numeric"'],
    'email'      => ['E-mel', 'email', 'maxlength="190" autocomplete="email"'],
    'phone'      => ['Nombor Telefon', 'tel', 'maxlength="20" pattern="\+?[0-9 \-]{7,20}" autocomplete="tel"'],
    'address'    => ['Alamat', 'text', 'maxlength="255" autocomplete="street-address"'],
    'occupation' => ['Pekerjaan', 'text', 'maxlength="100" autocomplete="organization-title"'],
];
?>
<main class="container narrow">
    <div class="card">
        <h1>Pendaftaran Peserta</h1>
        <p class="muted">Isi maklumat di bawah untuk mula belajar. Nama anda akan dicetak pada sijil seperti yang ditulis di sini.</p>
        <div class="alert alert-info">
            Sudah mendaftar sebelum ini, di peranti ini atau peranti lain?
            <a href="resume.php">Sambung Semula dengan e-mel anda</a>. Jika anda mendaftar semula dengan e-mel yang sama,
            kemajuan anda yang sedia ada akan disambung semula.
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">Sila betulkan ralat di bawah.</div>
        <?php elseif (!empty($formMessage)): ?>
            <div class="alert alert-error" role="alert"><?= e($formMessage) ?></div>
        <?php endif; ?>

        <form method="post" action="register.php" class="form">
            <?= csrf_field() ?>
            <?php foreach ($fields as $name => [$label, $type, $attrs]): ?>
                <div class="field<?= isset($errors[$name]) ? ' has-error' : '' ?>">
                    <label for="f-<?= e($name) ?>"><?= e($label) ?> <span class="req">*</span></label>
                    <input id="f-<?= e($name) ?>" name="<?= e($name) ?>" type="<?= e($type) ?>"
                           value="<?= e($old[$name] ?? '') ?>" required <?= $attrs ?>>
                    <?php if (isset($errors[$name])): ?>
                        <p class="field-error"><?= e($errors[$name]) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="field field-check">
                <label for="f-remember">
                    <input id="f-remember" name="remember" type="checkbox" value="1"<?= !empty($remember) ? ' checked' : '' ?>>
                    Ingat saya pada peranti ini selama 30 hari
                </label>
                <p class="muted small field-help">Tandakan hanya jika ini telefon atau komputer anda sendiri.
                    Di komputer awam atau dikongsi (contohnya di pusat komuniti atau kafe), biarkan kosong:
                    tekan "Selesai / Keluar" sebelum meninggalkan komputer. Menutup pelayar sahaja tidak
                    semestinya mengeluarkan anda. Anda juga akan dikeluarkan selepas 30 minit tanpa aktiviti.
                    Kemajuan anda tetap disimpan dan boleh disambung semula dengan e-mel anda.</p>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Hantar &amp; Mula Kursus</button>
        </form>
    </div>
</main>
