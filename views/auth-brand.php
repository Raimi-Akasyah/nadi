<?php
/** Navy brand panel beside the registration and "Sambung Semula" forms. */
require_once __DIR__ . '/icons.php';
?>
<aside class="auth-brand">
    <img class="blob blob-tr" src="assets/img/blob-orange.svg" alt="" aria-hidden="true" width="487" height="354">
    <img class="blob blob-bl" src="assets/img/blob-amber.svg" alt="" aria-hidden="true" width="425" height="309">
    <div class="auth-brand-inner">
        <p class="auth-kicker">Kursus Dalam Talian NADI</p>
        <p class="auth-brand-title">Belajar AI, langkah demi langkah</p>
        <ul class="auth-points">
            <li><span class="auth-point-icon"><?= icon('gift') ?></span><span><strong>Percuma</strong> Semua modul, kuiz dan sijil.</span></li>
            <li><span class="auth-point-icon"><?= icon('mail') ?></span><span><strong>Tiada kata laluan</strong> Sambung semula dengan e-mel anda sahaja.</span></li>
            <li><span class="auth-point-icon"><?= icon('award') ?></span><span><strong>Sijil digital</strong> Muat turun PDF selepas lulus kesemua kuiz.</span></li>
        </ul>
        <img class="auth-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294">
    </div>
</aside>
