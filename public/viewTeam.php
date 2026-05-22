<?php
    require '../config.php';
    session_start();

    $idT = isset($_GET['id']) ? $_GET['id'] : "";
    $teamInfo = null;

    if ($idT != "") {
        $sql = "SELECT s.idSquadra, s.nomeSquadra, s.nComponenti, sp.nomeAzienda 
                FROM squadre s 
                LEFT JOIN sponsor sp ON s.idSponsor = sp.idSponsor 
                JOIN tornei_squadre ts ON s.idSquadra = ts.idSquadra 
                WHERE ts.idTorneo = :id";
        $stm = $pdo->prepare($sql);
        $stm->bindParam(':id', $idT);
        $stm->execute();
        $teamInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Roster — Tech Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/php-pages.css">
    <style>
        body { display: block; padding-top: 100px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 24px; }
    </style>
</head>
<body>

    <nav class="glass-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo">Tech<span>Events</span></a>
            <div class="links">
                <a href="showeventstest.php">Dashboard</a>
                <a href="/logout.php" class="btn-secondary">Log Out</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <p class="section-label" style="text-align: left;">Participation</p>
        <h1 style="font-family: var(--font-display); font-size: 32px; margin-bottom: 40px;">Registered Teams</h1>

        <?php if ($teamInfo): ?>
            <table>
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Roster Size</th>
                        <th>Official Sponsor</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamInfo as $team): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= $team['nomeSquadra'] ?></td>
                            <td><?= $team['nComponenti'] ?> Players</td>
                            <td style="color: var(--primary); font-weight: 600;"><?= $team['nomeAzienda'] ?? 'Independent' ?></td>
                            <td>
                                <a href="addMember.php?id=<?= $team['idSquadra'] ?>" class="btn-primary" style="padding: 4px 12px; font-size: 12px;">Manage Roster</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="background: var(--surface); border: 1px solid var(--border); padding: 40px; border-radius: 8px; text-align: center;">
                <p style="margin: 0;">No teams registered for this competition yet.</p>
                <a href="signTeam.php?id=<?= $idT ?>" class="btn-primary" style="margin-top: 20px;">Register First Team</a>
            </div>
        <?php endif; ?>

        <div style="margin-top: 40px;">
            <a href="showeventstest.php" class="btn-secondary">← Back to Tournament Details</a>
        </div>
    </div>

    <footer style="margin-top: 120px;">
        <span class="footer-logo">Tech Events</span>
        <p>© 2026 — Professional Esports Systems</p>
    </footer>

</body>
</html>
