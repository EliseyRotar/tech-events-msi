<?php

require_once __DIR__ . '/src/EnvLoader.php';
require_once __DIR__ . '/src/Auth.php';

use App\EnvLoader;
use App\Auth;

EnvLoader::load(__DIR__ . '/.env');

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'tech_events';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET autocommit = 0');
} catch (PDOException $e) {
    die('Errore di connessione: ' . $e->getMessage());
}
