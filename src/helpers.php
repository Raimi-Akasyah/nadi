<?php
declare(strict_types=1);

const LEARNER_COOKIE = 'nadi_lms_token';
const LEARNER_COOKIE_DAYS = 30;

/** Escape a value for HTML output. */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redirect (302) to a path relative to the web root and stop. */
function redirect(string $path): void
{
    header('Location: ' . $path, true, 302);
    exit;
}

/**
 * Render views/<view>.php inside views/layout.php.
 * $vars are extracted into the view; 'title' and 'bodyClass' are also used by the layout.
 */
function render(string $view, array $vars = []): void
{
    $viewFile = APP_ROOT . '/views/' . $view . '.php';
    extract($vars, EXTR_SKIP);
    ob_start();
    require $viewFile;
    $content = ob_get_clean();
    require APP_ROOT . '/views/layout.php';
}

/** Send an HTTP error with a simple Malay page and stop. */
function abort(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    render('error', ['title' => 'Ralat ' . $status, 'status' => $status, 'message' => $message]);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent = $_POST['csrf'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    if (!is_string($sent) || !is_string($known) || $known === '' || !hash_equals($known, $sent)) {
        abort(400, 'Sesi tamat atau permintaan tidak sah. Sila muat semula halaman dan cuba lagi.');
    }
}

function post_only(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        abort(405, 'Kaedah permintaan tidak dibenarkan.');
    }
}

/** Format a 'Y-m-d H:i:s' datetime as e.g. "23 November 2026" with Malay month names. */
function format_date_ms(string $datetime): string
{
    static $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April', 5 => 'Mei', 6 => 'Jun',
        7 => 'Julai', 8 => 'Ogos', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember',
    ];
    $ts = strtotime($datetime);
    if ($ts === false) {
        return $datetime;
    }
    return date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/** The learner for this browser (session first, then the remember cookie), or null. */
function current_learner(): ?array
{
    static $resolved = false;
    static $learner = null;
    if ($resolved) {
        return $learner;
    }
    $resolved = true;

    if (isset($_SESSION['learner_id'])) {
        $learner = learner_find((int)$_SESSION['learner_id']);
        if ($learner !== null) {
            return $learner;
        }
        unset($_SESSION['learner_id']);
    }

    $raw = $_COOKIE[LEARNER_COOKIE] ?? '';
    if (is_string($raw) && preg_match('/^[0-9a-f]{64}$/', $raw)) {
        $learner = learner_find_by_token_hash(hash('sha256', $raw));
        if ($learner !== null) {
            session_regenerate_id(true);
            $_SESSION['learner_id'] = (int)$learner['id'];
        }
    }
    return $learner;
}

function require_learner(): array
{
    $learner = current_learner();
    if ($learner === null) {
        redirect('register.php');
    }
    return $learner;
}

function login_learner(int $id, string $rawToken): void
{
    session_regenerate_id(true);
    $_SESSION['learner_id'] = $id;
    setcookie(LEARNER_COOKIE, $rawToken, [
        'expires'  => time() + LEARNER_COOKIE_DAYS * 86400,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function forget_learner(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    setcookie(LEARNER_COOKIE, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
