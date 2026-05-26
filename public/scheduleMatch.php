<?php
require_once __DIR__ . '/../config.php';
\App\Auth::requireAdmin();

$idTorneo = isset($_GET['torneo']) ? (int)$_GET['torneo'] : 0;
if (!$idTorneo) {
    header('Location: /dashboard.php');
    exit;
}

$stm = $pdo->prepare(
    "SELECT t.*, e.nome AS eventName, e.discord_webhook
     FROM tornei t JOIN evento e ON e.idEvento = t.idEvento
     WHERE t.idTorneo = :id"
);
$stm->execute([':id' => $idTorneo]);
$tournament = $stm->fetch(\PDO::FETCH_ASSOC);
if (!$tournament) {
    header('Location: /dashboard.php');
    exit;
}

// Handle quick-set discord webhook for event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discord_webhook'])) {
    $hook = trim($_POST['discord_webhook']);
    if ($hook && !filter_var($hook, FILTER_VALIDATE_URL)) {
        $webhookError = 'Invalid Discord webhook URL.';
    } else {
        $pdo->prepare("UPDATE evento SET discord_webhook = NULLIF(:h,'') WHERE idEvento = :id")
            ->execute([':h' => $hook, ':id' => $tournament['idEvento']]);
        header('Location: /scheduleMatch.php?torneo=' . $idTorneo . '&msg=webhook');
        exit;
    }
}

// Handle enable check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enable_checkin'])) {
    $pdo->prepare(
        "UPDATE tornei SET checkin_opens_at = NOW(), status = 'checkin' WHERE idTorneo = :id"
    )->execute([':id' => $idTorneo]);
    header('Location: /scheduleMatch.php?torneo=' . $idTorneo . '&msg=checkin');
    exit;
}

// Fetch all matches
$stm2 = $pdo->prepare(
    "SELECT m.*,
            s1.nomeSquadra AS team1Name,
            s2.nomeSquadra AS team2Name
     FROM matches m
     LEFT JOIN squadre s1 ON s1.idSquadra = m.idSquadra1
     LEFT JOIN squadre s2 ON s2.idSquadra = m.idSquadra2
     WHERE m.idTorneo = :id
     ORDER BY m.round_number ASC, m.match_number ASC"
);
$stm2->execute([':id' => $idTorneo]);
$matches = $stm2->fetchAll(\PDO::FETCH_ASSOC);

// Fetch registered teams with seeds
$stm3 = $pdo->prepare(
    "SELECT ts.*, s.nomeSquadra,
            CASE WHEN c.idCheckin IS NOT NULL THEN 1 ELSE 0 END AS checked_in
     FROM tornei_squadre ts
     JOIN squadre s ON s.idSquadra = ts.idSquadra
     LEFT JOIN checkins c ON c.idTorneo = ts.idTorneo AND c.idSquadra = ts.idSquadra
     WHERE ts.idTorneo = :id
     ORDER BY COALESCE(ts.seed, 9999) ASC, s.nomeSquadra ASC"
);
$stm3->execute([':id' => $idTorneo]);
$teams = $stm3->fetchAll(\PDO::FETCH_ASSOC);

// Handle seed assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seeds'])) {
    foreach ($_POST['seeds'] as $squadraId => $seed) {
        $squadraId = (int)$squadraId;
        $seedVal   = (int)$seed;
        if ($squadraId <= 0) {
            continue;
        }
        $pdo->prepare(
            "UPDATE tornei_squadre SET seed = :s WHERE idTorneo = :t AND idSquadra = :sq"
        )->execute([':s' => $seedVal > 0 ? $seedVal : null, ':t' => $idTorneo, ':sq' => $squadraId]);
    }
    header('Location: /scheduleMatch.php?torneo=' . $idTorneo . '&msg=seeds');
    exit;
}

$msg = $_GET['msg'] ?? '';
$maxRound = !empty($matches) ? max(array_column($matches, 'round_number')) : 0;

$pageTitle = 'Manage Matches — ' . htmlspecialchars($tournament['nomeTorneo'], ENT_QUOTES) . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>
<link rel="stylesheet" href="/assets/css/bracket.css">

<main class="page-main">
<div class="container">

    <a href="/bracket.php?id=<?= $idTorneo ?>" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:32px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        <?= htmlspecialchars($tournament['nomeTorneo'], ENT_QUOTES) ?> Bracket
    </a>

    <?php if ($msg): ?>
    <div style="background:rgba(0,232,120,0.08);border:1px solid rgba(0,232,120,0.3);border-radius:var(--radius);padding:12px 20px;margin-bottom:24px;color:#00e878;font-size:14px;font-weight:600;">
        <?= $msg === 'webhook' ? '✓ Discord webhook saved.' : ($msg === 'seeds' ? '✓ Seeds updated.' : ($msg === 'checkin' ? '✓ Check-in window opened.' : '✓ Saved.')) ?>
    </div>
    <?php endif; ?>

    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:40px;">
        <div>
            <span class="section-label">Tournament Admin</span>
            <h1 style="font-size:clamp(22px,3.5vw,34px);font-weight:800;letter-spacing:-0.8px;">
                Manage — <?= htmlspecialchars($tournament['nomeTorneo'], ENT_QUOTES) ?>
            </h1>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if ($tournament['status'] === 'registration'): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="enable_checkin" value="1">
                <button type="submit" class="btn-secondary">Open Check-in Window</button>
            </form>
            <?php endif; ?>
            <?php if (empty($matches)): ?>
            <form method="POST" action="/generateBracket.php" style="display:inline;">
                <input type="hidden" name="idTorneo" value="<?= $idTorneo ?>">
                <button type="submit" class="btn-primary">⚡ Generate Bracket</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Seeds / Seeding -->
    <?php if (!empty($teams)): ?>
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;margin-bottom:32px;">
        <span class="section-label">Team Seeding</span>
        <h2 style="font-size:18px;font-weight:700;margin-bottom:20px;">Registered Teams</h2>
        <form method="POST">
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Team</th><th>Seed #</th><th>Checked In</th><th>Placement</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teams as $t): ?>
                        <tr>
                            <td style="font-weight:600;"><?= htmlspecialchars($t['nomeSquadra'], ENT_QUOTES) ?></td>
                            <td>
                                <input type="number" name="seeds[<?= (int)$t['idSquadra'] ?>]"
                                       value="<?= (int)$t['seed'] ?: '' ?>"
                                       min="1" placeholder="Auto"
                                       style="width:70px;background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:4px 8px;color:var(--text-primary);font-size:13px;">
                            </td>
                            <td>
                                <?php if ($t['checked_in']): ?>
                                <span class="badge badge-green">✓ Checked In</span>
                                <?php else: ?>
                                <span class="badge badge-gray">Not checked in</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-secondary);font-size:13px;">
                                <?= $t['placement'] ? '#' . $t['placement'] : '—' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" name="seeds" class="btn-primary" style="padding:8px 20px;">Save Seeds</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Discord webhook -->
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;margin-bottom:32px;">
        <span class="section-label">Notifications</span>
        <h2 style="font-size:18px;font-weight:700;margin-bottom:8px;">Discord Webhook</h2>
        <p style="color:var(--text-secondary);font-size:14px;margin-bottom:20px;">
            Receive match results and tournament updates directly in your Discord server.
        </p>
        <form method="POST" style="display:flex;gap:12px;flex-wrap:wrap;">
            <input class="form-input" type="url" name="discord_webhook"
                   placeholder="https://discord.com/api/webhooks/…"
                   value="<?= htmlspecialchars($tournament['discord_webhook'] ?? '', ENT_QUOTES) ?>"
                   style="flex:1;min-width:260px;">
            <button type="submit" class="btn-secondary" style="padding:10px 20px;">Save Webhook</button>
        </form>
        <?php if (!empty($webhookError ?? '')): ?>
        <div style="color:var(--danger);font-size:13px;margin-top:8px;"><?= htmlspecialchars($webhookError, ENT_QUOTES) ?></div>
        <?php endif; ?>
    </div>

    <!-- Match list -->
    <?php if (!empty($matches)): ?>
    <div class="reveal">
        <span class="section-label">Match Schedule</span>
        <h2 style="font-size:18px;font-weight:700;margin-bottom:20px;">All Matches</h2>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>Match</th>
                        <th>Team 1</th>
                        <th style="text-align:center">Score</th>
                        <th>Team 2</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matches as $m): ?>
                    <tr>
                        <td style="color:var(--text-secondary);font-size:12px;">
                            <?php
                            if ($m['round_number'] == $maxRound)         echo 'Final';
                            elseif ($m['round_number'] == $maxRound - 1) echo 'Semi-Final';
                            else                                          echo 'R' . $m['round_number'];
                            ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-secondary);">#<?= $m['match_number'] ?></td>
                        <td style="font-weight:600;font-size:14px;"><?= htmlspecialchars($m['team1Name'] ?? 'TBD', ENT_QUOTES) ?></td>
                        <td style="font-family:var(--font-display);font-weight:800;color:var(--accent-blue);text-align:center;">
                            <?= in_array($m['status'], ['completed','bye']) ? $m['punteggio1'] . ' — ' . $m['punteggio2'] : '—' ?>
                        </td>
                        <td style="font-weight:600;font-size:14px;"><?= htmlspecialchars($m['team2Name'] ?? 'TBD', ENT_QUOTES) ?></td>
                        <td>
                            <?php $sc = ['live'=>'badge-green','completed'=>'badge-gray','bye'=>'badge-gray','forfeit'=>'badge-gray','scheduled'=>'badge-blue']; ?>
                            <span class="badge <?= $sc[$m['status']] ?? 'badge-blue' ?>"><?= strtoupper($m['status']) ?></span>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content:flex-end;">
                                <a href="/match.php?id=<?= (int)$m['idMatch'] ?>" class="btn-secondary" style="padding:4px 10px;font-size:11px;">View</a>
                                <?php if (!in_array($m['status'], ['completed','bye','forfeit'])): ?>
                                <a href="/reportScore.php?id=<?= (int)$m['idMatch'] ?>" class="btn-primary" style="padding:4px 10px;font-size:11px;">Score</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
