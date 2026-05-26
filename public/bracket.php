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
    "SELECT t.*, e.nome AS eventName, e.idEvento, g.nomeGioco
     FROM tornei t
     JOIN evento e ON e.idEvento = t.idEvento
     LEFT JOIN giochi g ON g.idGioco = t.idGioco
     WHERE t.idTorneo = :id"
);
$stm->execute([':id' => $id]);
$tournament = $stm->fetch(PDO::FETCH_ASSOC);
if (!$tournament) {
    header('Location: /');
    exit;
}

$stm2 = $pdo->prepare(
    "SELECT m.*,
            s1.nomeSquadra AS team1Name,
            s2.nomeSquadra AS team2Name,
            sv.nomeSquadra AS winnerName
     FROM matches m
     LEFT JOIN squadre s1 ON s1.idSquadra = m.idSquadra1
     LEFT JOIN squadre s2 ON s2.idSquadra = m.idSquadra2
     LEFT JOIN squadre sv ON sv.idSquadra = m.idVincitore
     WHERE m.idTorneo = :id
     ORDER BY m.round_number ASC, m.match_number ASC"
);
$stm2->execute([':id' => $id]);
$allMatches = $stm2->fetchAll(PDO::FETCH_ASSOC);

$rounds    = [];
$maxRound  = 0;
foreach ($allMatches as $m) {
    $rounds[$m['round_number']][] = $m;
    if ($m['round_number'] > $maxRound) {
        $maxRound = (int)$m['round_number'];
    }
}

$bracketExists = !empty($allMatches);
$isAdmin       = isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1;

$msg = $_GET['msg'] ?? '';

$pageTitle = htmlspecialchars($tournament['nomeTorneo'], ENT_QUOTES) . ' — Bracket — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>
<link rel="stylesheet" href="/assets/css/bracket.css">

<main class="page-main">
<div class="container">

    <a href="/event.php?id=<?= (int)$tournament['idEvento'] ?>" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:32px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        <?= htmlspecialchars($tournament['eventName'], ENT_QUOTES) ?>
    </a>

    <?php if ($msg === 'generated'): ?>
    <div style="background:rgba(0,232,120,0.08);border:1px solid rgba(0,232,120,0.3);border-radius:var(--radius);padding:12px 20px;margin-bottom:24px;color:#00e878;font-size:14px;font-weight:600;">
        ✓ Bracket generated successfully. The tournament is now live!
    </div>
    <?php elseif ($msg === 'exists'): ?>
    <div style="background:rgba(255,59,48,0.08);border:1px solid rgba(255,59,48,0.3);border-radius:var(--radius);padding:12px 20px;margin-bottom:24px;color:var(--danger);font-size:14px;">
        A bracket already exists for this tournament.
    </div>
    <?php elseif ($msg === 'noteams'): ?>
    <div style="background:rgba(255,59,48,0.08);border:1px solid rgba(255,59,48,0.3);border-radius:var(--radius);padding:12px 20px;margin-bottom:24px;color:var(--danger);font-size:14px;">
        Not enough teams registered (minimum 2 required).
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;margin-bottom:40px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <span class="section-label">Bracket View</span>
                <h1 style="font-size:clamp(22px,3.5vw,36px);font-weight:800;letter-spacing:-0.8px;margin-bottom:12px;">
                    <?= htmlspecialchars($tournament['nomeTorneo'], ENT_QUOTES) ?>
                </h1>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <?php if ($tournament['nomeGioco']): ?>
                    <span class="badge badge-blue"><?= htmlspecialchars($tournament['nomeGioco'], ENT_QUOTES) ?></span>
                    <?php endif; ?>
                    <?php
                    $statusMap = ['registration'=>'badge-gray','checkin'=>'badge-blue','live'=>'badge-green','completed'=>'badge-gray'];
                    $sc = $statusMap[$tournament['status']] ?? 'badge-gray';
                    ?>
                    <span class="badge <?= $sc ?>" style="display:flex;align-items:center;gap:4px;">
                        <?php if ($tournament['status'] === 'live'): ?><span class="live-dot"></span><?php endif; ?>
                        <?= strtoupper(str_replace('_', ' ', $tournament['status'])) ?>
                    </span>
                    <span style="font-size:13px;color:var(--text-secondary);"><?= ucfirst(str_replace('_', ' ', $tournament['formato'])) ?></span>
                    <?php if ($tournament['montePremi']): ?>
                    <span style="font-family:var(--font-display);font-weight:800;color:var(--accent-blue);font-size:14px;">
                        €<?= number_format((float)$tournament['montePremi'], 0, '.', ',') ?> prize pool
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($isAdmin): ?>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php if (!$bracketExists && $tournament['status'] === 'registration'): ?>
                <form method="POST" action="/generateBracket.php" style="display:inline;">
                    <input type="hidden" name="idTorneo" value="<?= $id ?>">
                    <button type="submit" class="btn-primary">⚡ Generate Bracket</button>
                </form>
                <?php elseif ($bracketExists): ?>
                <a href="/scheduleMatch.php?torneo=<?= $id ?>" class="btn-secondary">Manage Matches</a>
                <?php endif; ?>
                <a href="/checkin.php?id=<?= $id ?>" class="btn-secondary">Check-ins</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$bracketExists): ?>
    <!-- No bracket yet -->
    <div style="background:var(--bg-secondary);border:1px dashed var(--border);border-radius:var(--radius-lg);padding:80px 40px;text-align:center;" class="reveal">
        <div style="font-size:56px;margin-bottom:20px;">🏆</div>
        <p style="font-family:var(--font-display);font-size:22px;font-weight:700;margin-bottom:8px;">Bracket Not Generated</p>
        <p style="color:var(--text-secondary);margin-bottom:28px;max-width:400px;margin-left:auto;margin-right:auto;">
            The tournament bracket will appear here once the organizer locks registrations and generates it.
        </p>
        <?php if ($isAdmin): ?>
        <form method="POST" action="/generateBracket.php" style="display:inline;">
            <input type="hidden" name="idTorneo" value="<?= $id ?>">
            <button type="submit" class="btn-primary">⚡ Generate Bracket Now</button>
        </form>
        <?php else: ?>
        <a href="/checkin.php?id=<?= $id ?>" class="btn-secondary">Check In Your Team</a>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- Bracket visualization -->
    <div class="reveal" style="margin-bottom:48px;">
        <span class="section-label">Competition Tree</span>
        <h2 style="font-size:20px;font-weight:700;letter-spacing:-0.5px;margin-bottom:20px;">Tournament Bracket</h2>
        <div class="bracket-scroll">
            <div class="bracket-container" id="bracketContainer">
                <?php for ($r = 1; $r <= $maxRound; $r++): ?>
                <div class="bracket-round" id="round-<?= $r ?>">
                    <div class="round-label">
                        <?php
                        if ($r === $maxRound)          echo 'Grand Final';
                        elseif ($r === $maxRound - 1)  echo 'Semi-Final';
                        elseif ($r === $maxRound - 2)  echo 'Quarter-Final';
                        else                           echo 'Round ' . $r;
                        ?>
                    </div>
                    <?php foreach ($rounds[$r] ?? [] as $m): ?>
                    <?php
                    $isWinner1 = $m['idVincitore'] && $m['idVincitore'] == $m['idSquadra1'];
                    $isWinner2 = $m['idVincitore'] && $m['idVincitore'] == $m['idSquadra2'];
                    ?>
                    <a href="/match.php?id=<?= (int)$m['idMatch'] ?>"
                       class="bracket-match <?= htmlspecialchars($m['status'], ENT_QUOTES) ?>">
                        <div class="match-team <?= $isWinner1 ? 'winner' : ($m['idVincitore'] && !$isWinner1 ? 'loser' : '') ?>">
                            <span class="team-name">
                                <?php if ($m['team1Name']): ?>
                                    <?= htmlspecialchars($m['team1Name'], ENT_QUOTES) ?>
                                <?php else: ?>
                                    <span class="tbd">TBD</span>
                                <?php endif; ?>
                            </span>
                            <span class="team-score">
                                <?= $m['status'] === 'completed' || $m['status'] === 'bye' ? $m['punteggio1'] : '' ?>
                            </span>
                        </div>
                        <div class="match-divider"></div>
                        <div class="match-team <?= $isWinner2 ? 'winner' : ($m['idVincitore'] && !$isWinner2 ? 'loser' : '') ?>">
                            <span class="team-name">
                                <?php if ($m['status'] === 'bye' && !$m['idSquadra2']): ?>
                                    <span class="bye-label">BYE</span>
                                <?php elseif ($m['team2Name']): ?>
                                    <?= htmlspecialchars($m['team2Name'], ENT_QUOTES) ?>
                                <?php else: ?>
                                    <span class="tbd">TBD</span>
                                <?php endif; ?>
                            </span>
                            <span class="team-score">
                                <?= $m['status'] === 'completed' || $m['status'] === 'bye' ? $m['punteggio2'] : '' ?>
                            </span>
                        </div>
                        <?php if ($m['stream_url']): ?>
                        <div class="match-stream-indicator" title="Stream available">▶</div>
                        <?php endif; ?>
                        <?php if ($m['scheduled_at'] && $m['status'] === 'scheduled'): ?>
                        <div class="match-time"><?= date('M j, H:i', strtotime($m['scheduled_at'])) ?></div>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Champion display -->
    <?php
    $champion = null;
    if ($tournament['status'] === 'completed' && !empty($rounds[$maxRound])) {
        $finalMatch = $rounds[$maxRound][0];
        if ($finalMatch['idVincitore'] && $finalMatch['winnerName']) {
            $champion = $finalMatch['winnerName'];
        }
    }
    ?>
    <?php if ($champion): ?>
    <div class="reveal" style="background:linear-gradient(135deg,rgba(255,215,0,0.05),rgba(255,215,0,0.02));border:1px solid rgba(255,215,0,0.3);border-radius:var(--radius-lg);padding:48px;text-align:center;margin-bottom:48px;">
        <div style="font-size:56px;margin-bottom:12px;">🏆</div>
        <span class="section-label" style="color:#FFD700;">Tournament Champion</span>
        <h2 style="font-family:var(--font-display);font-size:36px;font-weight:800;letter-spacing:-1px;color:#FFD700;">
            <?= htmlspecialchars($champion, ENT_QUOTES) ?>
        </h2>
    </div>
    <?php endif; ?>

    <!-- Match list -->
    <div class="reveal">
        <span class="section-label">All Matches</span>
        <h2 style="font-size:20px;font-weight:700;letter-spacing:-0.5px;margin-bottom:20px;">Schedule</h2>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>#</th>
                        <th>Team 1</th>
                        <th style="text-align:center">Score</th>
                        <th>Team 2</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allMatches as $m): ?>
                    <tr>
                        <td style="color:var(--text-secondary);font-size:12px;white-space:nowrap;">
                            <?php
                            if ($m['round_number'] == $maxRound)         echo 'Final';
                            elseif ($m['round_number'] == $maxRound - 1) echo 'Semi-Final';
                            elseif ($m['round_number'] == $maxRound - 2) echo 'QF';
                            else echo 'R' . $m['round_number'];
                            ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-secondary);">#<?= $m['match_number'] ?></td>
                        <td style="font-weight:600;font-size:14px;"><?= htmlspecialchars($m['team1Name'] ?? 'TBD', ENT_QUOTES) ?></td>
                        <td style="font-family:var(--font-display);font-weight:800;color:var(--accent-blue);text-align:center;">
                            <?= in_array($m['status'], ['completed','bye']) ? $m['punteggio1'] . ' — ' . $m['punteggio2'] : '—' ?>
                        </td>
                        <td style="font-weight:600;font-size:14px;"><?= htmlspecialchars($m['team2Name'] ?? 'TBD', ENT_QUOTES) ?></td>
                        <td>
                            <?php
                            $sc2 = ['live'=>'badge-green','completed'=>'badge-gray','bye'=>'badge-gray','forfeit'=>'badge-gray','scheduled'=>'badge-blue'];
                            ?>
                            <span class="badge <?= $sc2[$m['status']] ?? 'badge-gray' ?>" style="display:inline-flex;align-items:center;gap:4px;">
                                <?= $m['status'] === 'live' ? '<span class="live-dot"></span>' : '' ?>
                                <?= strtoupper($m['status']) ?>
                            </span>
                        </td>
                        <td style="color:var(--text-secondary);font-size:12px;white-space:nowrap;">
                            <?= $m['scheduled_at'] ? date('M j, H:i', strtotime($m['scheduled_at'])) : '—' ?>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content:flex-end;">
                                <a href="/match.php?id=<?= (int)$m['idMatch'] ?>" class="btn-secondary" style="padding:4px 10px;font-size:11px;">View</a>
                                <?php if ($isAdmin && !in_array($m['status'], ['completed','bye','forfeit'])): ?>
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

<script>
// Give each round vertical breathing room proportional to round index
document.addEventListener('DOMContentLoaded', () => {
    const rounds = document.querySelectorAll('.bracket-round');
    rounds.forEach((round, i) => {
        const matchSpacing = Math.pow(2, i) * 8;
        round.querySelectorAll('.bracket-match + .bracket-match').forEach(m => {
            m.style.marginTop = matchSpacing + 'px';
        });
        // Center round vertically within container
        const firstMatch = round.querySelector('.bracket-match');
        if (firstMatch && i > 0) {
            round.style.paddingTop = (matchSpacing / 2) + 'px';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
