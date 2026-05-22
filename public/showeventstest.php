<?php
    require '../config.php';
    session_start();
    
    $eventID = isset($_GET['id']) ? $_GET['id'] : "";
    $tournamentInfo = null;
    
    $sql = 'SELECT * FROM evento';
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $generalInfo = $stm->fetchAll(PDO::FETCH_ASSOC);

    if ($eventID != "") {
        $sql = 'SELECT * FROM tornei WHERE tornei.idEvento = :id';
        $stm = $pdo->prepare($sql);
        $stm->bindParam(':id', $eventID);
        $stm->execute();
        $tournamentInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Dashboard — Tech Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/php-pages.css">
    <style>
        body { display: block; padding-top: 100px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 24px; }
        .page-header { margin-bottom: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
        .auth-nav { margin-bottom: 20px; text-align: right; }
        .auth-nav a { margin-left: 20px; font-size: 14px; font-weight: 600; }
        h2 { text-align: left; margin-bottom: 20px; font-size: 24px; }
        .btn-small { padding: 6px 12px; font-size: 12px; }
        .admin-actions { margin-top: 40px; display: flex; gap: 12px; }
    </style>
</head>
<body>

    <nav class="glass-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo">Tech<span>Events</span></a>
            <div class="links">
                <a href="/assets/storici/storico.html">Storico</a>
                <a href="/logout.php" class="btn-secondary">Log Out</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div>
                <p class="section-label" style="text-align: left;">Management</p>
                <h1 style="font-family: var(--font-display); font-size: 32px; margin: 0;">Active Events</h1>
            </div>
            <div class="auth-nav">
                <?php if(!isset($_SESSION['email'])): ?>
                    <a href="sign_in.php">Sign In</a>
                    <a href="login.php">Log In</a>
                <?php endif; ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($generalInfo as $row): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= $row['nome'] ?></td>
                        <td><?= $row['dataInizio'] ?></td>
                        <td><?= $row['dataFine'] ?></td>
                        <td><?= $row['citta'] ?>, <?= $row['paese'] ?></td>
                        <td><?= $row['nPosti'] ?></td>
                        <td>   
                            <a href="showeventstest.php?id=<?= $row["idEvento"]?>" class="btn-primary" style="padding: 4px 12px; font-size: 12px;">View Details</a>
                            <?php if(isset($_SESSION['admin']) && $_SESSION['admin'] == '1'): ?>
                                <a href="addTournament.php?id=<?= $row["idEvento"]?>" class="btn-secondary" style="padding: 4px 12px; font-size: 12px; margin-left: 8px;">Add Tournament</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($tournamentInfo != null): ?>
            <div style="margin-top: 80px;">
                <p class="section-label" style="text-align: left;">Competition Details</p>
                <h2>Tournaments</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Tournament</th>
                            <th>Prize Pool</th>
                            <th>Schedule</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tournamentInfo as $row): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= $row['nomeTorneo']?></td>
                                <td style="color: var(--accent); font-weight: 700;">$<?= number_format($row['montePremi'], 0) ?></td>
                                <td><?= $row['giornoSvolgimento']?></td>
                                <td>
                                    <a href="viewTeam.php?id=<?= $row['idTorneo'] ?>" class="btn-secondary" style="padding: 4px 12px; font-size: 12px;">View Teams</a>
                                    <a href="signTeam.php?id=<?= $row['idTorneo'] ?>" class="btn-primary" style="padding: 4px 12px; font-size: 12px; margin-left: 8px;">Register Team</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['admin']) && $_SESSION['admin'] == true): ?>
            <div class="admin-actions">
                <a href="createEvent.php" class="btn-primary">Create New Event</a>
                <a href="addGame.php" class="btn-secondary">Register New Discipline</a>
            </div>
        <?php endif ?>
        <div style="margin-top: 20px;">
            <a href="addTeam.php" class="btn-secondary">Register New Team</a>
        </div>
    </div>

    <footer style="margin-top: 120px;">
        <span class="footer-logo">Tech Events</span>
        <p>© 2026 — Professional Esports Systems</p>
    </footer>

</body>
</html>
