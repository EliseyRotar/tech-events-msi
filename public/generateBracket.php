<?php
require_once __DIR__ . '/../config.php';
\App\Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard.php');
    exit;
}

$idTorneo = isset($_POST['idTorneo']) ? (int)$_POST['idTorneo'] : 0;
if (!$idTorneo) {
    header('Location: /dashboard.php');
    exit;
}

$stm = $pdo->prepare("SELECT t.*, e.discord_webhook FROM tornei t JOIN evento e ON e.idEvento = t.idEvento WHERE t.idTorneo = :id");
$stm->execute([':id' => $idTorneo]);
$tournament = $stm->fetch(PDO::FETCH_ASSOC);
if (!$tournament) {
    header('Location: /dashboard.php');
    exit;
}

$stm = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE idTorneo = :id");
$stm->execute([':id' => $idTorneo]);
if ($stm->fetchColumn() > 0) {
    header('Location: /bracket.php?id=' . $idTorneo . '&msg=exists');
    exit;
}

// Fetch registered teams; prefer checked-in if check-in window was opened
$hasCheckin = !empty($tournament['checkin_opens_at']);
if ($hasCheckin) {
    $stm = $pdo->prepare(
        "SELECT ts.idSquadra, ts.seed, s.nomeSquadra
         FROM tornei_squadre ts
         JOIN squadre s ON s.idSquadra = ts.idSquadra
         JOIN checkins c ON c.idTorneo = ts.idTorneo AND c.idSquadra = ts.idSquadra
         WHERE ts.idTorneo = :id
         ORDER BY COALESCE(ts.seed, 9999) ASC, s.nomeSquadra ASC"
    );
} else {
    $stm = $pdo->prepare(
        "SELECT ts.idSquadra, ts.seed, s.nomeSquadra
         FROM tornei_squadre ts
         JOIN squadre s ON s.idSquadra = ts.idSquadra
         WHERE ts.idTorneo = :id
         ORDER BY COALESCE(ts.seed, 9999) ASC, s.nomeSquadra ASC"
    );
}
$stm->execute([':id' => $idTorneo]);
$teams = $stm->fetchAll(PDO::FETCH_ASSOC);

$n = count($teams);
if ($n < 2) {
    header('Location: /bracket.php?id=' . $idTorneo . '&msg=noteams');
    exit;
}

// Assign seeds if missing
foreach ($teams as $i => &$team) {
    if (empty($team['seed'])) {
        $team['seed'] = $i + 1;
    }
}
unset($team);
usort($teams, fn($a, $b) => (int)$a['seed'] <=> (int)$b['seed']);

// Calculate bracket parameters
$bracketSize = 1;
while ($bracketSize < $n) {
    $bracketSize *= 2;
}
$rounds = (int)log($bracketSize, 2);

// Standard seeding: expand [1,2] into full bracket positions
$positions = bracketSeedPositions($bracketSize);

// Map seed → team ID (seeds start at 1)
$teamsBySeed = [];
foreach ($teams as $t) {
    $teamsBySeed[(int)$t['seed']] = (int)$t['idSquadra'];
}

// Build slot array: position index → team ID or null (bye)
$slots = [];
foreach ($positions as $seed) {
    $slots[] = isset($teamsBySeed[$seed]) ? $teamsBySeed[$seed] : null;
}

try {
    $pdo->beginTransaction();

    // Insert all match shells for all rounds
    $matchIds = []; // [round][matchNum] → idMatch

    for ($round = 1; $round <= $rounds; $round++) {
        $matchCount = $bracketSize >> $round;
        for ($mn = 1; $mn <= $matchCount; $mn++) {
            $sq1    = null;
            $sq2    = null;
            $status = 'scheduled';

            if ($round === 1) {
                $idx1 = ($mn - 1) * 2;
                $idx2 = $idx1 + 1;
                $sq1  = $slots[$idx1] ?? null;
                $sq2  = $slots[$idx2] ?? null;
                if ($sq1 !== null && $sq2 === null) {
                    $status = 'bye';
                } elseif ($sq1 === null && $sq2 === null) {
                    $status = 'bye';
                }
            }

            $stm = $pdo->prepare(
                "INSERT INTO matches (idTorneo, round_number, match_number, idSquadra1, idSquadra2, status)
                 VALUES (:t, :r, :mn, :s1, :s2, :st)"
            );
            $stm->execute([
                ':t'  => $idTorneo,
                ':r'  => $round,
                ':mn' => $mn,
                ':s1' => $sq1,
                ':s2' => $sq2,
                ':st' => $status,
            ]);
            $matchIds[$round][$mn] = (int)$pdo->lastInsertId();
        }
    }

    // Wire up next_match_id links
    for ($round = 1; $round < $rounds; $round++) {
        $matchCount = $bracketSize >> $round;
        for ($mn = 1; $mn <= $matchCount; $mn++) {
            $nextMn   = (int)ceil($mn / 2);
            $nextSlot = (($mn % 2) === 1) ? 1 : 2;
            $pdo->prepare(
                "UPDATE matches SET next_match_id = :nm, next_match_slot = :ns WHERE idMatch = :id"
            )->execute([
                ':nm' => $matchIds[$round + 1][$nextMn],
                ':ns' => $nextSlot,
                ':id' => $matchIds[$round][$mn],
            ]);
        }
    }

    // Auto-advance byes in round 1
    $matchCount1 = $bracketSize >> 1;
    for ($mn = 1; $mn <= $matchCount1; $mn++) {
        $idx1 = ($mn - 1) * 2;
        $sq1  = $slots[$idx1] ?? null;
        $sq2  = $slots[$idx1 + 1] ?? null;
        if ($sq1 !== null && $sq2 === null) {
            $mid = $matchIds[1][$mn];
            $pdo->prepare(
                "UPDATE matches SET idVincitore = :v, punteggio1 = 1, punteggio2 = 0, status = 'bye' WHERE idMatch = :id"
            )->execute([':v' => $sq1, ':id' => $mid]);
            placeWinner($pdo, $mid);
        } elseif ($sq1 === null && $sq2 !== null) {
            $mid = $matchIds[1][$mn];
            $pdo->prepare(
                "UPDATE matches SET idVincitore = :v, punteggio1 = 0, punteggio2 = 1, status = 'bye' WHERE idMatch = :id"
            )->execute([':v' => $sq2, ':id' => $mid]);
            placeWinner($pdo, $mid);
        }
    }

    // Mark tournament live
    $pdo->prepare("UPDATE tornei SET status = 'live' WHERE idTorneo = :id")->execute([':id' => $idTorneo]);

    $pdo->commit();
} catch (\Exception $e) {
    $pdo->rollBack();
    header('Location: /bracket.php?id=' . $idTorneo . '&msg=error');
    exit;
}

// Discord notification
if (!empty($tournament['discord_webhook'])) {
    \App\Discord::tournamentStart($tournament['discord_webhook'], $tournament['nomeTorneo'], $n);
}

header('Location: /bracket.php?id=' . $idTorneo . '&msg=generated');
exit;

// ── Helpers ──────────────────────────────────────────────────────────────────

function bracketSeedPositions(int $size): array
{
    $positions = [1, 2];
    while (count($positions) < $size) {
        $next  = [];
        $total = count($positions) * 2 + 1;
        foreach ($positions as $p) {
            $next[] = $p;
            $next[] = $total - $p;
        }
        $positions = $next;
    }
    return $positions;
}

function placeWinner(\PDO $pdo, int $idMatch): void
{
    $stm = $pdo->prepare(
        "SELECT idVincitore, next_match_id, next_match_slot FROM matches WHERE idMatch = :id"
    );
    $stm->execute([':id' => $idMatch]);
    $m = $stm->fetch(\PDO::FETCH_ASSOC);
    if (!$m || !$m['next_match_id'] || !$m['idVincitore']) {
        return;
    }
    $col = $m['next_match_slot'] == 1 ? 'idSquadra1' : 'idSquadra2';
    // $col is either 'idSquadra1' or 'idSquadra2' — safe whitelist
    $pdo->prepare("UPDATE matches SET {$col} = :v WHERE idMatch = :id")
        ->execute([':v' => $m['idVincitore'], ':id' => $m['next_match_id']]);
}
