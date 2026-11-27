<?php
/** @var string|null $error */
require_once __DIR__ . '/icons.php';
?>
<main class="container narrow page-pad">
    <div class="card admin-login">
        <div class="verify-head">
            <span class="verify-shield"><?= icon('lock') ?></span>
            <div>
                <h1>Panel Admin</h1>
                <p class="muted">Untuk penganjur kursus sahaja.</p>
            </div>
        </div>
        <?php if ($error !== null): ?>
            <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="admin.php" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="login">
            <div class="field">
                <label for="a-password">Kata laluan</label>
                <input id="a-password" name="password" type="password" autocomplete="current-password" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Log Masuk <?= icon('arrow') ?></button>
        </form>
    </div>
</main>
