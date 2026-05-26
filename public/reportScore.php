<?php
require_once __DIR__ . '/../config.php';
\App\Auth::requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: /dashboard.php');
    exit;
}

$stm = $pdo->prepare(
    "SELECT m.*,
            s1.nomeSquadra AS team1Name,
            s2.nomeSquadra AS team2Name,
            t.nomeTorneo, t.idTorneo,
            e.discord_webhook
     FROM matches m
     LEFT JOIN squadre s1 ON s1.idSquadra = m.idSquadra1
     LEFT JOIN squadre s2 ON s2.idSquadra = m.idSquadra2
     JOIN tornei t ON t.idTorneo = m.idTorneo
     JOIN evento e ON e.idEvento = t.idEvento
     WHERE m.idMatch = :id"
);
$stm->execute([':id' => $id]);
$match = $stm->fetch(\PDO::FETCH_ASSOC);
if (!$match || in_array($match['status'], ['completed', 'bye', 'forfeit'])) {
    header('Location: /bracket.php?id=' . ($match['idTorneo'] ?? '') . '&msg=locked');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score1    = max(0, (int)($_POST['score1'] ?? 0));
    $score2    = max(0, (int)($_POST['score2'] ?? 0));
    $status    = in_array($_POST['action'] ?? '', ['live', 'completed', 'forfeit']) ? $_POST['action'] : 'scheduled';
    $streamUrl = trim($_POST['stream_url'] ?? '');
    $schedAt   = !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null;

    if ($streamUrl && !filter_var($streamUrl, FILTER_VALIDATE_URL)) {
        $error = 'Stream URL is not valid.';
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();

            if ($status === 'completed' || $status === 'forfeit') {
                if ($score1 === $score2) {
                    $error = 'A tie is not allowed in elimination brackets. One team must win.';
                    $pdo->rollBack();
                } else {
                    $winner = $score1 > $score2 ? $match['idSquadra1'] : $match['idSquadra2'];

                    $pdo->prepare(
                        "UPDATE matches
                         SET punteggio1 = :s1, punteggio2 = :s2, idVincitore = :w,
                             status = :st, completed_at = NOW(),
                             stream_url = NULLIF(:su, ''), scheduled_at = :sa
                         WHERE idMatch = :id"
                    )->execute([
                        ':s1' => $score1, ':s2' => $score2, ':w' => $winner,
                        ':st' => $status, ':su' => $streamUrl, ':sa' => $schedAt, ':id' => $id,
                    ]);

                    // Advance winner to next match
                    $stmNext = $pdo->prepare(
                        "SELECT next_match_id, next_match_slot FROM matches WHERE idMatch = :id"
                    );
                    $stmNext->execute([':id' => $id]);
                    $next = $stmNext->fetch(\PDO::FETCH_ASSOC);

                    if ($next && $next['next_match_id']) {
                        $col = (int)$next['next_match_slot'] === 1 ? 'idSquadra1' : 'idSquadra2';
                        if (!in_array($col, ['idSquadra1', 'idSquadra2'], true)) {
                            throw new \Exception('Invalid next_match_slot value.');
                        }
                        $pdo->prepare("UPDATE matches SET {$col} = :v WHERE idMatch = :nm")
                            ->execute([':v' => $winner, ':nm' => $next['next_match_id']]);
                    } else {
                        // This was the final match — mark tournament completed and set placement
                        $loser = $winner == $match['idSquadra1'] ? $match['idSquadra2'] : $match['idSquadra1'];
                        $pdo->prepare(
                            "UPDATE tornei SET status = 'completed' WHERE idTorneo = :t"
                        )->execute([':t' => $match['idTorneo']]);
                        $pdo->prepare(
                            "UPDATE tornei_squadre SET placement = 1 WHERE idTorneo = :t AND idSquadra = :s"
                        )->execute([':t' => $match['idTorneo'], ':s' => $winner]);
                        $pdo->prepare(
                            "UPDATE tornei_squadre SET placement = 2 WHERE idTorneo = :t AND idSquadra = :s"
                        )->execute([':t' => $match['idTorneo'], ':s' => $loser]);

                        // Discord: tournament winner
                        if (!empty($match['discord_webhook'])) {
                            $winnerName = $score1 > $score2 ? $match['team1Name'] : $match['team2Name'];
                            \App\Discord::tournamentWinner(
                                $match['discord_webhook'],
                                $match['nomeTorneo'],
                                $winnerName ?? 'Unknown'
                            );
                        }
                    }

                    $pdo->commit();

                    // Discord: match result
                    if (!empty($match['discord_webhook'])) {
                        $matchUrl = 'https://tech-events-msi.onrender.com/match.php?id=' . $id;
                        \App\Discord::matchResult(
                            $match['discord_webhook'],
                            $match['nomeTorneo'],
                            $match['team1Name'] ?? 'TBD',
                            $score1,
                            $match['team2Name'] ?? 'TBD',
                            $score2,
                            $matchUrl
                        );
                    }

                    header('Location: /bracket.php?id=' . $match['idTorneo'] . '&msg=scored');
                    exit;
                }
            } else {
                // Update status/schedule/stream only
                $pdo->prepare(
                    "UPDATE matches SET status = :st,
                     stream_url = NULLIF(:su, ''), scheduled_at = :sa
                     WHERE idMatch = :id"
                )->execute([':st' => $status, ':su' => $streamUrl, ':sa' => $schedAt, ':id' => $id]);
                $pdo->commit();
                header('Location: /bracket.php?id=' . $match['idTorneo'] . '&msg=updated');
                exit;
            }
        } catch (\Exception $e) {
            $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Report Score — ' . htmlspecialchars($match['nomeTorneo'], ENT_QUOTES) . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>
<link rel="stylesheet" href="/assets/css/bracket.css">

<main class="page-main">
<div class="container" style="max-width:600px;">
    <a href="/bracket.php?id=<?= (int)$match['idTorneo'] ?>" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:32px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        <?= htmlspecialchars($match['nomeTorneo'], ENT_QUOTES) ?> Bracket
    </a>

    <div class="form-card" style="margin-top:0;">
        <span class="section-label">Match Management</span>
        <h1 style="font-family:var(--font-display);font-size:24px;font-weight:700;letter-spacing:-0.8px;margin-bottom:8px;">Report Score</h1>
        <p style="color:var(--text-secondary);font-size:14px;margin-bottom:28px;">
            <?= htmlspecialchars($match['team1Name'] ?? 'TBD', ENT_QUOTES) ?>
            <span style="color:var(--accent-blue);margin:0 8px;">vs</span>
            <?= htmlspecialchars($match['team2Name'] ?? 'TBD', ENT_QUOTES) ?>
        </p>

        <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label"><?= htmlspecialchars($match['team1Name'] ?? 'Team 1', ENT_QUOTES) ?></label>
                    <input class="form-input" type="number" name="score1" min="0"
                           value="<?= (int)$match['punteggio1'] ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= htmlspecialchars($match['team2Name'] ?? 'Team 2', ENT_QUOTES) ?></label>
                    <input class="form-input" type="number" name="score2" min="0"
                           value="<?= (int)$match['punteggio2'] ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Match Status</label>
                <select class="form-select form-input" name="action">
                    <option value="scheduled" <?= $match['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="live" <?= $match['status'] === 'live' ? 'selected' : '' ?>>🔴 Set Live</option>
                    <option value="completed">✓ Mark Completed (finalises scores)</option>
                    <option value="forfeit">⚠ Forfeit</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Scheduled Date & Time</label>
                <input class="form-input" type="datetime-local" name="scheduled_at"
                       value="<?= $match['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($match['scheduled_at'])) : '' ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Stream URL (optional)</label>
                <input class="form-input" type="url" name="stream_url"
                       placeholder="https://twitch.tv/…"
                       value="<?= htmlspecialchars($match['stream_url'] ?? '', ENT_QUOTES) ?>">
            </div>

            <button type="submit" class="btn-primary btn-submit">Save Match</button>
        </form>
    </div>
</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
