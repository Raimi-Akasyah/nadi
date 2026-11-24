<?php
declare(strict_types=1);

const LEARNER_COOKIE = 'nadi_lms_token';
const LEARNER_COOKIE_DAYS = 30;
// A learner session ends after this many minutes without a page request.
const LEARNER_IDLE_MINUTES = 30;

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

/**
 * True when a learner session has been idle for longer than LEARNER_IDLE_MINUTES.
 * Sessions without a recorded last activity (0) count as expired.
 */
function learner_session_expired(int $lastActive, int $now): bool
{
    return $now - $lastActive > LEARNER_IDLE_MINUTES * 60;
}

/** The learner for this browser (session first, then the "remember me" cookie), or null. */
function current_learner(): ?array
{
    static $resolved = false;
    static $learner = null;
    if ($resolved) {
        return $learner;
    }
    $resolved = true;

    if (isset($_SESSION['learner_id'])) {
        if (learner_session_expired((int)($_SESSION['last_active'] ?? 0), time())) {
            // Idle too long: end the session so the next person on a shared computer starts clean.
            unset($_SESSION['learner_id'], $_SESSION['last_active']);
            session_regenerate_id(true);
        } else {
            $learner = learner_find((int)$_SESSION['learner_id']);
            if ($learner !== null) {
                $_SESSION['last_active'] = time();
                return $learner;
            }
            unset($_SESSION['learner_id'], $_SESSION['last_active']);
        }
    }

    $raw = $_COOKIE[LEARNER_COOKIE] ?? '';
    if (is_string($raw) && preg_match('/^[0-9a-f]{64}$/', $raw)) {
        $learner = learner_find_by_token_hash(hash('sha256', $raw));
        if ($learner !== null) {
            session_regenerate_id(true);
            $_SESSION['learner_id'] = (int)$learner['id'];
            $_SESSION['last_active'] = time();
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

/** Set (or, with an empty value, delete) the "remember me" cookie. */
function set_learner_cookie(string $value, int $expires): void
{
    setcookie(LEARNER_COOKIE, $value, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Sign the learner in for this browser session. Only when $remember is true (the learner ticked
 * "Ingat saya" on their own device) is a persistent cookie set; otherwise any earlier one is removed.
 */
function login_learner(int $id, string $rawToken, bool $remember): void
{
    session_regenerate_id(true);
    $_SESSION['learner_id'] = $id;
    $_SESSION['last_active'] = time();
    if ($remember) {
        set_learner_cookie($rawToken, time() + LEARNER_COOKIE_DAYS * 86400);
    } else {
        set_learner_cookie('', time() - 3600);
    }
}

function forget_learner(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    set_learner_cookie('', time() - 3600);
}
