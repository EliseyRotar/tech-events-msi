<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');

require_once __DIR__ . '/../../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo json_encode(['error' => 'Missing id']);
    exit;
}

try {
    $stm = $pdo->prepare(
        "SELECT idMatch, punteggio1, punteggio2, status, idVincitore,
                idSquadra1, idSquadra2, stream_url, scheduled_at
         FROM matches WHERE idMatch = :id"
    );
    $stm->execute([':id' => $id]);
    $match = $stm->fetch(\PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    http_response_code(503);
    echo json_encode(['error' => 'unavailable', 'status' => 503]);
    exit;
}

if (!$match) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

echo json_encode([
    'idMatch'     => (int)$match['idMatch'],
    'punteggio1'  => (int)$match['punteggio1'],
    'punteggio2'  => (int)$match['punteggio2'],
    'status'      => $match['status'],
    'idVincitore' => $match['idVincitore'] ? (int)$match['idVincitore'] : null,
    'stream_url'  => $match['stream_url'],
]);
