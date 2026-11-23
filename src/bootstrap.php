<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('APP_ROOT', dirname(__DIR__));

/**
 * Load configuration from config/config.php, or fall back to environment variables.
 */
function load_config(): ?array
{
    $file = APP_ROOT . '/config/config.php';
    if (is_file($file)) {
        $cfg = require $file;
        return is_array($cfg) ? $cfg : null;
    }
    $host = getenv('DB_HOST');
    $name = getenv('DB_NAME');
    $user = getenv('DB_USER');
    if ($host === false || $name === false || $user === false) {
        return null;
    }
    $pass = getenv('DB_PASS');
    $port = getenv('DB_PORT');
    return [
        'db' => [
            'host'    => $host,
            'port'    => $port !== false && $port !== '' ? (int)$port : 3306,
            'name'    => $name,
            'user'    => $user,
            'pass'    => $pass !== false ? $pass : '',
            'charset' => 'utf8mb4',
        ],
        'app' => [
            'name'     => 'NADI LMS',
            'timezone' => 'Asia/Kuala_Lumpur',
        ],
        'certificate' => [
            'issuer' => 'NADI',
        ],
    ];
}

$GLOBALS['config'] = load_config();
if ($GLOBALS['config'] === null) {
    error_log('NADI LMS: configuration missing (config/config.php or DB_* env vars).');
    if (PHP_SAPI !== 'cli') {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><html lang="ms"><head><meta charset="utf-8"><title>Ralat</title></head>'
        . '<body><h1>Ralat pelayan</h1><p>Sistem belum dikonfigurasi. Sila hubungi pentadbir.</p></body></html>';
    exit;
}

function config(): array
{
    return $GLOBALS['config'];
}

date_default_timezone_set(config()['app']['timezone'] ?? 'Asia/Kuala_Lumpur');

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
}

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_name('nadi_lms_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/repositories.php';
require_once __DIR__ . '/Course.php';
