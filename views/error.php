<?php
require_once __DIR__ . '/icons.php';
?>
<main class="container narrow page-pad">
    <div class="card error-card">
        <div class="error-art" aria-hidden="true">
            <img class="error-blob" src="assets/img/blob-amber.svg" alt="" width="425" height="309">
            <span class="error-code"><?= e($status) ?></span>
            <img class="error-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294">
        </div>
        <h1>Ralat <?= e($status) ?></h1>
        <p><?= e($message) ?></p>
        <p><a class="btn btn-primary btn-lg" href="index.php"><?= icon('home') ?>Pergi ke Laman Utama</a></p>
    </div>
</main>
