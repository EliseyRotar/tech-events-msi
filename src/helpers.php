<?php

/**
 * Execute a callable within a PDO transaction.
 * On success, optionally redirect and exit.
 * On any failure, roll back and return the error message.
 */
function runInTransaction(PDO $pdo, callable $action, ?string $redirect = null): ?string
{
    try {
        $pdo->beginTransaction();
        $action();
        $pdo->commit();
        if ($redirect !== null) {
            // Only allow relative paths to prevent open redirect
            if (!preg_match('#^/[^/]#', $redirect) && $redirect !== '/') {
                throw new \InvalidArgumentException('Redirect must be a relative path');
            }
            header('Location: ' . $redirect);
            exit;
        }
        return null;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return $e->getMessage();
    }
}

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $submitted = $_POST['csrf_token'] ?? '';
    $stored    = $_SESSION['csrf_token'] ?? '';
    if ($stored === '' || !hash_equals($stored, $submitted)) {
        http_response_code(403);
        exit('Invalid security token. Please go back and try again.');
    }
}

/**
 * Load translation strings based on the 'lang' cookie.
 * Defaults to Italian ('it') if not set or unsupported.
 */
function loadTranslations(): array
{
    $lang = $_COOKIE['lang'] ?? 'it';
    $supported = ['it', 'en'];
    if (!in_array($lang, $supported, true)) {
        $lang = 'it';
    }
    $file = __DIR__ . "/../lang/{$lang}.php";
    if (is_file($file)) {
        $result = include $file;
        return is_array($result) ? $result : [];
    }
    return [];
}

function t(string $key): string
{
    static $translations = null;
    if ($translations === null) {
        $translations = loadTranslations();
    }
    return $translations[$key] ?? $key;
}
