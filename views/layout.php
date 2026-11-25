<?php
/** @var string $content */
$pageTitle = isset($title) && $title !== '' ? $title . ' | ' . config()['app']['name'] : config()['app']['name'];
$bodyClass = $bodyClass ?? '';
$hideHeader = $hideHeader ?? false;
?><!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#232F3E">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
    <link rel="preload" href="assets/fonts/nunito-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="assets/fonts/fredoka-latin-600-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="assets/css/app.css">
    <script>document.documentElement.classList.add('js');</script>
</head>
<body class="<?= e($bodyClass) ?>">
<?php if (!$hideHeader): ?>
<header class="site-header">
    <div class="site-header-inner">
        <a class="site-logo" href="index.php"><img src="assets/img/logo.svg" alt="NADI" width="108" height="36"></a>
        <?php require __DIR__ . '/partners.php'; ?>
    </div>
</header>
<?php endif; ?>
<?= $content ?>
<?php if (!$hideHeader): ?>
<footer class="site-footer">
    <div class="container site-footer-inner">
        <?php require __DIR__ . '/partners.php'; ?>
        <div class="site-footer-meta">
            <p class="site-footer-name"><?= e(config()['app']['name']) ?></p>
            <p class="small">Kursus AI percuma untuk komuniti. <a href="verify.php">Semak kesahihan sijil</a></p>
        </div>
    </div>
</footer>
<?php endif; ?>
<?php if (!empty($scripts)): foreach ($scripts as $src): ?>
<script src="<?= e($src) ?>" defer></script>
<?php endforeach; endif; ?>
</body>
</html>
