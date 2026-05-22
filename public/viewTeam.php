<?php
require '../config.php';
\App\Auth::requireLogin();

$idT      = isset($_GET['id']) ? (int)$_GET['id'] : null;
$teamInfo = null;

if ($idT) {
    $stm = $pdo->prepare(
        "SELECT s.idSquadra, s.nomeSquadra, s.nComponenti, sp.nomeAzienda
         FROM squadre s
         LEFT JOIN sponsor sp ON s.idSponsor = sp.idSponsor
         JOIN tornei_squadre ts ON s.idSquadra = ts.idSquadra
         WHERE ts.idTorneo = :id
         ORDER BY s.nomeSquadra"
    );
    $stm->bindParam(':id', $idT);
    $stm->execute();
    $teamInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Rosters — Tech Dragons Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body>

<div id="page-overlay" aria-hidden="true"><span class="overlay-logo">TD</span></div>
<div id="cursor-dot"  aria-hidden="true"></div>
<div id="cursor-ring" aria-hidden="true"></div>

<nav class="glass-nav scrolled" id="navbar">
    <div class="nav-container">
        <a href="/" class="nav-logo">Tech<span>Dragons</span></a>
        <div class="links">
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php" class="btn-secondary" style="padding:8px 18px;">Sign Out</a>
        </div>
    </div>
</nav>

<main class="page-main">
    <div class="container">
        <div class="page-header">
            <div class="page-header-left">
                <span class="section-label">Participation</span>
                <h1>Registered Teams</h1>
            </div>
            <div class="page-header-actions">
                <?php if ($idT): ?>
                    <a href="/signTeam.php?id=<?= $idT ?>" class="btn-primary">Register Another Team</a>
                <?php endif; ?>
                <a href="/dashboard.php" class="btn-secondary">← Dashboard</a>
            </div>
        </div>

        <?php if ($teamInfo && count($teamInfo) > 0): ?>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Roster Size</th>
                        <th>Sponsor</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamInfo as $team): ?>
                    <tr>
                        <td style="font-weight:600;font-family:var(--font-display);">
                            <?= htmlspecialchars($team['nomeSquadra'], ENT_QUOTES) ?>
                        </td>
                        <td>
                            <span class="badge badge-blue"><?= (int)$team['nComponenti'] ?> players</span>
                        </td>
                        <td style="color:var(--accent-blue);font-weight:600;">
                            <?= htmlspecialchars($team['nomeAzienda'] ?? 'Independent', ENT_QUOTES) ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="/addMember.php?id=<?= (int)$team['idSquadra'] ?>" class="btn-primary" style="padding:6px 14px;font-size:12px;">
                                    Manage Roster
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="background:var(--bg-secondary);border:1px dashed var(--border);border-radius:var(--radius-lg);padding:60px;text-align:center;">
            <p style="font-family:var(--font-display);font-size:20px;font-weight:700;margin-bottom:8px;">No teams registered yet</p>
            <p style="color:var(--text-secondary);margin-bottom:24px;">Be the first organisation to enter this tournament.</p>
            <?php if ($idT): ?>
                <a href="/signTeam.php?id=<?= $idT ?>" class="btn-primary">Register First Team</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="footer-inner">
        <div class="footer-bottom" style="border-top:none;padding-top:0;">
            <span class="footer-logo" style="font-family:var(--font-display);font-weight:700;">Tech<span style="color:var(--accent-blue)">Dragons</span></span>
            <p>&copy; <?= date('Y') ?> Tech Dragons Events</p>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
