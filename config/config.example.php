<?php
/**
 * Copy this file to config/config.php and fill in your database credentials.
 * config/config.php is gitignored and must never be committed.
 * Alternatively, set the env vars DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS.
 */
return [
    'db' => [
        'host'    => 'localhost',
        'port'    => 3306,
        'name'    => 'your_database_name',
        'user'    => 'your_database_user',
        'pass'    => 'your_database_password',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'     => 'NADI LMS',
        'timezone' => 'Asia/Kuala_Lumpur',
    ],
    'certificate' => [
        'issuer' => 'NADI',
    ],
    // Panel admin (https://your-domain/admin.php). Create the hash with: php bin/admin-password.php
    // and paste it between single quotes. Leave it empty to turn the panel off (admin.php then answers 404).
    'admin' => [
        'password_hash' => '',
    ],
];
