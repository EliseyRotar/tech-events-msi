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
            header("Location: $redirect");
            exit;
        }
        return null;
    } catch (\Throwable $e) {
        $pdo->rollBack();
        return $e->getMessage();
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
