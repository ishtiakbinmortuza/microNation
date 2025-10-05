<?php
// Global configuration for Qamarshan portal
// Adjust these values for your environment.

$QAMAR_DB = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'qamarshan_cms',
    'user' => 'root',          // CHANGE in production
    'pass' => '',              // CHANGE in production
    'charset' => 'utf8mb4'
];

// Security settings
const QAMAR_PASSWORD_ALGO = PASSWORD_BCRYPT; // or PASSWORD_DEFAULT (PHP 8.3+ may choose Argon2i/2id)
const QAMAR_SESSION_NAME  = 'QAMARSHAN_SESSID';

// File upload settings
const QAMAR_UPLOAD_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
const QAMAR_MAX_PHOTO_BYTES = 2 * 1024 * 1024; // 2MB
const QAMAR_MAX_ATTACHMENT_BYTES = 5 * 1024 * 1024; // 5MB

// Ensure uploads directory exists (best-effort)
if (!is_dir(QAMAR_UPLOAD_DIR)) {
    @mkdir(QAMAR_UPLOAD_DIR, 0775, true);
}

// Start session early
if (session_status() === PHP_SESSION_NONE) {
    session_name(QAMAR_SESSION_NAME);
    session_start();
}

function db(): PDO {
    static $pdo = null; 
    global $QAMAR_DB;   
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $QAMAR_DB['host'], $QAMAR_DB['port'], $QAMAR_DB['name'], $QAMAR_DB['charset']);
        $pdo = new PDO($dsn, $QAMAR_DB['user'], $QAMAR_DB['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function sanitize(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function is_logged_in(): bool { return !empty($_SESSION['user']); }
function current_user() { return $_SESSION['user'] ?? null; }
function require_admin(): void {
    if (!is_logged_in() || (current_user()['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo '<h2>Forbidden</h2><p>Admin access required.</p>';
        exit;
    }
}

// Flash messaging helpers
function flash_set(string $key, string $message): void { $_SESSION['flash'][$key] = $message; }
function flash_get(string $key): ?string {
    if (!isset($_SESSION['flash'][$key])) return null;
    $msg = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}
