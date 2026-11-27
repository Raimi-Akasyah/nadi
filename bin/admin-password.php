<?php
declare(strict_types=1);

// Creates the admin password hash for config/config.php ('admin' => ['password_hash' => '...']) or for the
// environment variable ADMIN_PASSWORD_HASH. Only the hash is stored, never the password.
// Run from the project folder: php bin/admin-password.php   (type the password, then press Enter)

if (PHP_SAPI !== 'cli') {
    exit(1);
}
fwrite(STDOUT, 'Kata laluan admin (sekurang-kurangnya 12 aksara): ');
$password = rtrim((string)fgets(STDIN), "\r\n");
if (strlen($password) < 12) {
    fwrite(STDERR, "Kata laluan terlalu pendek.\n");
    exit(1);
}
fwrite(STDOUT, password_hash($password, PASSWORD_DEFAULT) . "\n");
