<?php

namespace App;

class Auth
{
    public static function requireLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['email'])) {
            header('Location: /login.php');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (!isset($_SESSION['admin']) || (int)$_SESSION['admin'] !== 1) {
            header('Location: /index.php');
            exit;
        }
    }
}
