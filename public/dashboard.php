<?php
require_once '../config.php';
\App\Auth::requireLogin();

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

$pageTitle = "Event Dashboard — Tech Dragons Events";
require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container" style="margin-top: 100px; padding-bottom: 120px;">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 48px;">
        <div>
            <p class="section-label" style="text-align: left;">Management Portal</p>
            <h1 style="font-family: var(--font-display); font-size: 32px; margin: 0; font-weight: 800;">Global Events Archive</h1>
        </div>
        <?php if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
        <div class="admin-actions" style="display: flex; gap: 12px;">
            <a href="createEvent.php" class="btn-primary">Create Event</a>
            <a href="addGame.php" class="btn-secondary">New Discipline</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="table-container" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(255,255,255,0.03);">
                    <th style="padding: 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Event Name</th>
                    <th style="padding: 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Duration</th>
                    <th style="padding: 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Location</th>
                    <th style="padding: 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Capacity</th>
                    <th style="padding: 20px; text-align: right; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($generalInfo as $row): ?>
                    <tr style="border-top: 1px solid var(--border); transition: var(--transition);">
                        <td style="padding: 20px; font-weight: 600;"><?= htmlspecialchars($row['nome']) ?></td>
                        <td style="padding: 20px; color: var(--text-muted); font-size: 14px;">
                            <?= htmlspecialchars($row['dataInizio']) ?> <span style="color: var(--primary);">→</span> <?= htmlspecialchars($row['dataFine']) ?>
                        </td>
                        <td style="padding: 20px; font-size: 14px;"><?= htmlspecialchars($row['citta']) ?>, <?= htmlspecialchars($row['paese']) ?></td>
                        <td style="padding: 20px; font-size: 14px;">
                            <span style="color: var(--accent); font-weight: 700;"><?= $row['nPosti'] ?></span> slots
                        </td>
                        <td style="padding: 20px; text-align: right;">   
                            <a href="dashboard.php?id=<?= $row["idEvento"]?>" class="btn-primary" style="padding: 6px 12px; font-size: 12px;">View Details</a>
                            <?php if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                                <a href="addTournament.php?id=<?= $row["idEvento"]?>" class="btn-secondary" style="padding: 6px 12px; font-size: 12px; margin-left: 8px;">Add Tournament</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($tournamentInfo != null): ?>
        <div style="margin-top: 80px;">
            <p class="section-label" style="text-align: left;">Competition Layer</p>
            <h2 style="text-align: left; margin-bottom: 32px; font-size: 28px;">Active Tournaments</h2>
            
            <div class="table-container" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.03);">
                            <th style="padding: 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Tournament</th>
                            <th style="padding: 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Prize Pool</th>
                            <th style="padding: 20px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Scheduled Date</th>
                            <th style="padding: 20px; text-align: right; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tournamentInfo as $row): ?>
                            <tr style="border-top: 1px solid var(--border);">
                                <td style="padding: 20px; font-weight: 600;"><?= htmlspecialchars($row['nomeTorneo']) ?></td>
                                <td style="padding: 20px; color: var(--accent); font-weight: 800;">$<?= number_format($row['montePremi'], 0) ?></td>
                                <td style="padding: 20px; font-size: 14px;"><?= htmlspecialchars($row['giornoSvolgimento']) ?></td>
                                <td style="padding: 20px; text-align: right;">
                                    <a href="viewTeam.php?id=<?= $row['idTorneo'] ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 12px;">Rosters</a>
                                    <a href="signTeam.php?id=<?= $row['idTorneo'] ?>" class="btn-primary" style="padding: 6px 12px; font-size: 12px; margin-left: 8px;">Register Team</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div style="margin-top: 48px; border-top: 1px solid var(--border); padding-top: 32px; display: flex; gap: 16px;">
        <a href="addTeam.php" class="btn-secondary">Register New Organization</a>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
