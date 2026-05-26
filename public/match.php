<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: /');
    exit;
}

$stm = $pdo->prepare(
    "SELECT m.*,
            s1.nomeSquadra AS team1Name, s1.idSquadra AS t1id,
            s2.nomeSquadra AS team2Name, s2.idSquadra AS t2id,
            sv.nomeSquadra AS winnerName,
            t.nomeTorneo, t.idTorneo, t.idEvento,
            e.nome AS eventName, g.nomeGioco
     FROM matches m
     LEFT JOIN squadre s1  ON s1.idSquadra = m.idSquadra1
     LEFT JOIN squadre s2  ON s2.idSquadra = m.idSquadra2
     LEFT JOIN squadre sv  ON sv.idSquadra = m.idVincitore
     JOIN tornei t         ON t.idTorneo   = m.idTorneo
     JOIN evento e         ON e.idEvento   = t.idEvento
     LEFT JOIN giochi g    ON g.idGioco    = t.idGioco
     WHERE m.idMatch = :id"
);
$stm->execute([':id' => $id]);
$match = $stm->fetch(PDO::FETCH_ASSOC);
if (!$match) {
    header('Location: /');
    exit;
}

$isAdmin  = isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1;
$stm3 = $pdo->prepare("SELECT MAX(round_number) FROM matches WHERE idTorneo = :t");
$stm3->execute([':t' => $match['idTorneo']]);
$maxRound = (int)$stm3->fetchColumn();

$roundLabel = 'Round ' . $match['round_number'];
if ($match['round_number'] === $maxRound)         $roundLabel = 'Grand Final';
elseif ($match['round_number'] === $maxRound - 1) $roundLabel = 'Semi-Final';
elseif ($match['round_number'] === $maxRound - 2) $roundLabel = 'Quarter-Final';

$pageTitle = ($match['team1Name'] ?? 'TBD') . ' vs ' . ($match['team2Name'] ?? 'TBD') . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>
<link rel="stylesheet" href="/assets/css/bracket.css">

<main class="page-main">
<div class="container" style="max-width:880px;">

    <a href="/bracket.php?id=<?= (int)$match['idTorneo'] ?>" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:32px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        <?= htmlspecialchars($match['nomeTorneo'], ENT_QUOTES) ?> Bracket
    </a>

    <!-- Match header meta -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;align-items:center;">
        <span class="section-label" style="margin:0;"><?= htmlspecialchars($roundLabel, ENT_QUOTES) ?></span>
        <?php if ($match['nomeGioco']): ?>
        <span class="badge badge-blue"><?= htmlspecialchars($match['nomeGioco'], ENT_QUOTES) ?></span>
        <?php endif; ?>
        <?php
        $statusColors = ['live'=>'badge-green','completed'=>'badge-gray','bye'=>'badge-gray','forfeit'=>'badge-gray','scheduled'=>'badge-blue'];
        ?>
        <span class="badge <?= $statusColors[$match['status']] ?? 'badge-blue' ?>" style="display:inline-flex;align-items:center;gap:5px;">
            <?= $match['status'] === 'live' ? '<span class="live-dot"></span>' : '' ?>
            <?= strtoupper($match['status']) ?>
        </span>
        <?php if ($match['scheduled_at']): ?>
        <span style="font-size:13px;color:var(--text-secondary);">
            <?= date('D, M j Y · H:i', strtotime($match['scheduled_at'])) ?>
        </span>
        <?php endif; ?>
    </div>

    <!-- VS card -->
    <div class="match-vs-card reveal">
        <!-- Team 1 -->
        <div class="match-team-side">
            <?php if ($match['t1id']): ?>
            <a href="/team.php?id=<?= (int)$match['t1id'] ?>" style="text-decoration:none;color:inherit;">
            <?php endif; ?>
            <div class="match-team-avatar" style="<?= $match['idVincitore'] == $match['idSquadra1'] ? 'border-color:#FFD700;background:rgba(255,215,0,0.1);color:#FFD700;' : '' ?>">
                <?= strtoupper(substr($match['team1Name'] ?? '?', 0, 2)) ?>
            </div>
            <div class="match-team-name"><?= htmlspecialchars($match['team1Name'] ?? 'TBD', ENT_QUOTES) ?></div>
            <?php if ($match['idVincitore'] == $match['idSquadra1']): ?>
            <div style="margin-top:8px;font-size:12px;font-weight:700;color:#FFD700;">🏆 WINNER</div>
            <?php endif; ?>
            <?php if ($match['t1id']): ?></a><?php endif; ?>
        </div>

        <!-- Score -->
        <div class="match-score-center">
            <div class="match-score-display" id="scoreDisplay">
                <?php if (in_array($match['status'], ['completed','bye','live'])): ?>
                <span id="score1"><?= (int)$match['punteggio1'] ?></span>
                <span class="match-score-sep"> — </span>
                <span id="score2"><?= (int)$match['punteggio2'] ?></span>
                <?php else: ?>
                <span style="font-size:32px;color:var(--text-secondary);">VS</span>
                <?php endif; ?>
            </div>
            <div class="match-vs-label"><?= htmlspecialchars($roundLabel, ENT_QUOTES) ?></div>
            <?php if ($match['stream_url']): ?>
            <a href="<?= htmlspecialchars($match['stream_url'], ENT_QUOTES) ?>"
               target="_blank" rel="noopener"
               class="btn-primary"
               style="margin-top:16px;display:inline-flex;align-items:center;gap:6px;padding:8px 20px;font-size:13px;">
                ▶ Watch Live
            </a>
            <?php endif; ?>
        </div>

        <!-- Team 2 -->
        <div class="match-team-side">
            <?php if ($match['t2id']): ?>
            <a href="/team.php?id=<?= (int)$match['t2id'] ?>" style="text-decoration:none;color:inherit;">
            <?php endif; ?>
            <div class="match-team-avatar" style="<?= $match['idVincitore'] == $match['idSquadra2'] ? 'border-color:#FFD700;background:rgba(255,215,0,0.1);color:#FFD700;' : '' ?>">
                <?= strtoupper(substr($match['team2Name'] ?? '?', 0, 2)) ?>
            </div>
            <div class="match-team-name"><?= htmlspecialchars($match['team2Name'] ?? 'TBD', ENT_QUOTES) ?></div>
            <?php if ($match['idVincitore'] == $match['idSquadra2']): ?>
            <div style="margin-top:8px;font-size:12px;font-weight:700;color:#FFD700;">🏆 WINNER</div>
            <?php endif; ?>
            <?php if ($match['t2id']): ?></a><?php endif; ?>
        </div>
    </div>

    <!-- Admin actions -->
    <?php if ($isAdmin && !in_array($match['status'], ['completed','bye','forfeit'])): ?>
    <div class="reveal" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:32px;">
        <a href="/reportScore.php?id=<?= $id ?>" class="btn-primary">📋 Report Score</a>
        <a href="/scheduleMatch.php?torneo=<?= (int)$match['idTorneo'] ?>" class="btn-secondary">Manage Match</a>
    </div>
    <?php endif; ?>

    <!-- Match details -->
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;margin-bottom:32px;">
        <span class="section-label">Match Details</span>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:20px;margin-top:16px;">
            <div>
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Tournament</div>
                <div style="font-weight:600;font-size:14px;">
                    <a href="/bracket.php?id=<?= (int)$match['idTorneo'] ?>" style="color:var(--accent-blue);">
                        <?= htmlspecialchars($match['nomeTorneo'], ENT_QUOTES) ?>
                    </a>
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Event</div>
                <div style="font-weight:600;font-size:14px;">
                    <a href="/event.php?id=<?= (int)$match['idEvento'] ?>" style="color:var(--accent-blue);">
                        <?= htmlspecialchars($match['eventName'], ENT_QUOTES) ?>
                    </a>
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Stage</div>
                <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($roundLabel, ENT_QUOTES) ?></div>
            </div>
            <?php if ($match['scheduled_at']): ?>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Scheduled</div>
                <div style="font-weight:600;font-size:14px;"><?= date('M j Y, H:i', strtotime($match['scheduled_at'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($match['completed_at']): ?>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Completed</div>
                <div style="font-weight:600;font-size:14px;"><?= date('M j Y, H:i', strtotime($match['completed_at'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($match['stream_url']): ?>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Stream</div>
                <div style="font-weight:600;font-size:14px;">
                    <a href="<?= htmlspecialchars($match['stream_url'], ENT_QUOTES) ?>" target="_blank" rel="noopener" style="color:var(--accent-blue);">Watch ↗</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</main>

<?php if ($match['status'] === 'live'): ?>
<script>
// Poll for live score updates every 10 seconds
(function poll() {
    setTimeout(async function() {
        try {
            const res = await fetch('/api/matches.php?id=<?= $id ?>');
            const data = await res.json();
            if (data.punteggio1 !== undefined) {
                document.getElementById('score1').textContent = data.punteggio1;
                document.getElementById('score2').textContent = data.punteggio2;
                if (data.status === 'completed') {
                    location.reload();
                    return;
                }
            }
        } catch (e) { /* ignore */ }
        poll();
    }, 10000);
}());
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
