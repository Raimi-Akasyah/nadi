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
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="<?= e($bodyClass) ?>">
<?php if (!$hideHeader): ?>
<header class="site-header">
    <a class="site-logo" href="index.php"><img src="assets/img/logo.svg" alt="NADI" height="36"></a>
</header>
<?php endif; ?>
<?= $content ?>
<?php if (!empty($scripts)): foreach ($scripts as $src): ?>
<script src="<?= e($src) ?>" defer></script>
<?php endforeach; endif; ?>
</body>
</html>
