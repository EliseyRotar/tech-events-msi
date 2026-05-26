<?php
require_once '../config.php';
\App\Auth::requireLogin();

$eventID       = isset($_GET['id']) ? (int)$_GET['id'] : null;
$tournamentInfo = null;

$stm = $pdo->prepare(
    'SELECT e.*,
            (SELECT COUNT(*) FROM tornei t WHERE t.idEvento = e.idEvento) AS tournamentCount
     FROM evento e
     ORDER BY e.dataInizio DESC'
);
$stm->execute();
$generalInfo = $stm->fetchAll(PDO::FETCH_ASSOC);

if ($eventID) {
    $stm = $pdo->prepare(
        'SELECT t.*, g.nomeGioco
         FROM tornei t
         LEFT JOIN giochi g ON g.idGioco = t.idGioco
         WHERE t.idEvento = :id
         ORDER BY t.giornoSvolgimento ASC'
    );
    $stm->bindParam(':id', $eventID);
    $stm->execute();
    $tournamentInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = 'Dashboard — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
    <div class="container">

        <!-- Page header -->
        <div class="page-header reveal">
            <div class="page-header-left">
                <span class="section-label">Management Portal</span>
                <h1>Global Events Archive</h1>
            </div>
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
            <div class="page-header-actions">
                <a href="/createEvent.php" class="btn-primary">+ Create Event</a>
                <a href="/addGame.php" class="btn-secondary">Add Discipline</a>
                <a href="/create-news.php" class="btn-secondary">+ News Post</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Events table -->
        <?php if (empty($generalInfo)): ?>
        <div style="background:var(--bg-secondary);border:1px dashed var(--border);border-radius:var(--radius-lg);padding:60px;text-align:center;" class="reveal">
            <p style="font-family:var(--font-display);font-size:20px;font-weight:700;margin-bottom:8px;">No events yet</p>
            <p style="color:var(--text-secondary);margin-bottom:24px;">Events created by admins will appear here.</p>
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                <a href="/createEvent.php" class="btn-primary">Create First Event</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="data-table-wrap reveal">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Dates</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Tournaments</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($generalInfo as $row): ?>
                    <tr <?= $eventID == $row['idEvento'] ? 'style="background:rgba(0,212,255,0.04);"' : '' ?>>
                        <td>
                            <span style="font-weight:700;font-family:var(--font-display);font-size:15px;">
                                <?= htmlspecialchars($row['nome'], ENT_QUOTES) ?>
                            </span>
                        </td>
                        <td style="color:var(--text-secondary);font-size:13px;">
                            <?= htmlspecialchars($row['dataInizio'], ENT_QUOTES) ?>
                            <span style="color:var(--accent-blue);margin:0 4px;">→</span>
                            <?= htmlspecialchars($row['dataFine'], ENT_QUOTES) ?>
                        </td>
                        <td style="font-size:14px;">
                            <?php
                                $loc = trim(implode(', ', array_filter([
                                    $row['citta'] ?? '',
                                    $row['paese'] ?? '',
                                ])));
                                echo $loc ? htmlspecialchars($loc, ENT_QUOTES) : '<span style="color:var(--text-secondary)">Online</span>';
                            ?>
                        </td>
                        <td>
                            <span class="badge badge-blue"><?= (int)$row['nPosti'] ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $row['tournamentCount'] > 0 ? 'badge-green' : 'badge-gray' ?>">
                                <?= (int)$row['tournamentCount'] ?> active
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="/dashboard.php?id=<?= (int)$row['idEvento'] ?>" class="btn-secondary" style="padding:6px 12px;font-size:12px;">
                                    <?= $eventID == $row['idEvento'] ? 'Viewing' : 'View' ?>
                                </a>
                                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                                <a href="/addTournament.php?id=<?= (int)$row['idEvento'] ?>" class="btn-primary" style="padding:6px 12px;font-size:12px;">
                                    + Tournament
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Tournament detail (when event is selected) -->
        <?php if ($tournamentInfo !== null): ?>
        <div style="margin-top:64px;" class="reveal">
            <div class="page-header" style="margin-bottom:24px;">
                <div class="page-header-left">
                    <span class="section-label">Competition Layer</span>
                    <h2 style="text-align:left;font-size:28px;margin-bottom:0;">Active Tournaments</h2>
                </div>
            </div>

            <?php if (empty($tournamentInfo)): ?>
            <div style="background:var(--bg-secondary);border:1px dashed var(--border);border-radius:var(--radius-lg);padding:40px;text-align:center;">
                <p style="color:var(--text-secondary);">No tournaments created for this event yet.</p>
                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                    <a href="/addTournament.php?id=<?= $eventID ?>" class="btn-primary" style="margin-top:16px;display:inline-flex;">+ Add Tournament</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tournament</th>
                            <th>Game</th>
                            <th>Prize Pool</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tournamentInfo as $row): ?>
                        <tr>
                            <td style="font-weight:700;font-family:var(--font-display);">
                                <?= htmlspecialchars($row['nomeTorneo'], ENT_QUOTES) ?>
                            </td>
                            <td>
                                <span class="badge badge-blue"><?= htmlspecialchars($row['nomeGioco'] ?? '—', ENT_QUOTES) ?></span>
                            </td>
                            <td style="font-family:var(--font-display);font-weight:700;color:var(--accent-blue);">
                                <?= $row['montePremi'] ? '€' . number_format((float)$row['montePremi'], 0, '.', ',') : '<span style="color:var(--text-secondary)">TBA</span>' ?>
                            </td>
                            <td style="color:var(--text-secondary);font-size:13px;">
                                <?= htmlspecialchars($row['giornoSvolgimento'], ENT_QUOTES) ?>
                            </td>
                            <td>
                                <?php
                                $tStatus = $row['status'] ?? 'registration';
                                $tBadge  = ['registration'=>'badge-gray','checkin'=>'badge-blue','live'=>'badge-green','completed'=>'badge-gray'];
                                $tLabel  = ['registration'=>'Registration','checkin'=>'Check-in','live'=>'Live','completed'=>'Done'];
                                ?>
                                <span class="badge <?= $tBadge[$tStatus] ?? 'badge-gray' ?>" style="display:inline-flex;align-items:center;gap:4px;">
                                    <?php if ($tStatus === 'live'): ?><span class="live-dot"></span><?php endif; ?>
                                    <?= htmlspecialchars($tLabel[$tStatus] ?? ucfirst($tStatus), ENT_QUOTES) ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="/bracket.php?id=<?= (int)$row['idTorneo'] ?>" class="btn-secondary" style="padding:6px 12px;font-size:12px;">Bracket</a>
                                    <a href="/viewTeam.php?id=<?= (int)$row['idTorneo'] ?>" class="btn-secondary" style="padding:6px 12px;font-size:12px;">Rosters</a>
                                    <a href="/signTeam.php?id=<?= (int)$row['idTorneo'] ?>" class="btn-primary" style="padding:6px 12px;font-size:12px;">Register Team</a>
                                    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                                    <a href="/scheduleMatch.php?torneo=<?= (int)$row['idTorneo'] ?>" class="btn-secondary" style="padding:6px 12px;font-size:12px;">Manage</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Bottom actions -->
        <hr class="section-divider">
        <div class="page-header-actions">
            <a href="/addTeam.php" class="btn-secondary">Register New Organisation</a>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
