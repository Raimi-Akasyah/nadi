<?php
/** @var array $old */
/** @var array $errors */
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

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">Sila betulkan ralat di bawah.</div>
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
            <button type="submit" class="btn btn-primary btn-block">Hantar &amp; Mula Kursus</button>
        </form>
    </div>
</main>
