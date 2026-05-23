<?php

require_once __DIR__ . '/src/EnvLoader.php';
require_once __DIR__ . '/src/Auth.php';

use App\EnvLoader;
use App\Auth;

EnvLoader::load(__DIR__ . '/.env');

$host   = getenv('DB_HOST') ?: 'localhost';
$port   = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'tech_dragons_events';
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS') ?: '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $ssl_opts = [
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
        $ssl_opts[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
    }
    $pdo = new PDO($dsn, $user, $pass, $ssl_opts);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Errore di connessione: ' . $e->getMessage());
}
