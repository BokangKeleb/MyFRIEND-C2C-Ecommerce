<?php

// Automatically detects whether the project is in htdocs root or a subfolder.
if (!defined('APP_BASE_URL')) {
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $projectRoot = realpath(__DIR__ . '/..');
    $baseUrl = '';

    if ($documentRoot && $projectRoot && strpos($projectRoot, $documentRoot) === 0) {
        $relative = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
        $baseUrl = rtrim($relative, '/');
    }

    define('APP_BASE_URL', $baseUrl);
}

function app_url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return APP_BASE_URL . ($path === '/' ? '/' : $path);
}

function absolute_url(string $path = ''): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . app_url($path);
}

function redirect_to(string $path): void
{
    header('Location: ' . app_url($path));
    exit();
}

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function ensure_session_started(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function csrf_token(): string
{
    ensure_session_started();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    ensure_session_started();
    $submitted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if ($submitted === '' || $stored === '' || !hash_equals($stored, $submitted)) {
        http_response_code(419);
        die('Your session expired. Please go back, refresh the page and try again.');
    }
}

function is_local_environment(): bool
{
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $host = explode(':', $host)[0];
    return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
}
