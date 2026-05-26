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
    "SELECT t.*, e.nome AS eventName, e.idEvento
     FROM tornei t JOIN evento e ON e.idEvento = t.idEvento
     WHERE t.idTorneo = :id"
);
$stm->execute([':id' => $id]);
$tournament = $stm->fetch(\PDO::FETCH_ASSOC);
if (!$tournament) {
    header('Location: /');
    exit;
}

$isAdmin   = isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1;
$loggedIn  = isset($_SESSION['email']);

// Find current user's team(s) in this tournament
$myTeams = [];
if ($loggedIn) {
    $stm2 = $pdo->prepare(
        "SELECT ts.idSquadra, s.nomeSquadra, m.is_captain,
                CASE WHEN c.idCheckin IS NOT NULL THEN 1 ELSE 0 END AS already_checked_in
         FROM membri m
         JOIN utenti u       ON u.idUtente  = m.idUtente
         JOIN squadre s      ON s.idSquadra = m.idSquadra
         JOIN tornei_squadre ts ON ts.idSquadra = m.idSquadra AND ts.idTorneo = :t
         LEFT JOIN checkins c ON c.idTorneo = :t2 AND c.idSquadra = m.idSquadra
         WHERE u.email = :email"
    );
    $stm2->execute([':t' => $id, ':t2' => $id, ':email' => $_SESSION['email']]);
    $myTeams = $stm2->fetchAll(\PDO::FETCH_ASSOC);
}

// Handle check-in action
$msg   = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $loggedIn) {
    $squadraId = (int)($_POST['idSquadra'] ?? 0);

    // Verify this user is actually a member of that team in this tournament
    $allowed = false;
    foreach ($myTeams as $mt) {
        if ((int)$mt['idSquadra'] === $squadraId) {
            $allowed = true;
            break;
        }
    }
    if ($isAdmin) {
        $allowed = true;
    }

    if ($allowed && $squadraId) {
        try {
            $pdo->prepare(
                "INSERT IGNORE INTO checkins (idTorneo, idSquadra) VALUES (:t, :s)"
            )->execute([':t' => $id, ':s' => $squadraId]);
            $msg = 'checked_in';
            // Refresh my teams list after check-in
            if (isset($stm2)) {
                $stm2->execute([':t' => $id, ':t2' => $id, ':email' => $_SESSION['email']]);
                $myTeams = $stm2->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Exception $e) {
            $error = 'Check-in failed. Please try again.';
        }
    } else {
        $error = 'You are not authorised to check in this team.';
    }
}

// All teams with check-in status
$stm3 = $pdo->prepare(
    "SELECT ts.idSquadra, s.nomeSquadra, ts.seed,
            CASE WHEN c.idCheckin IS NOT NULL THEN 1 ELSE 0 END AS checked_in,
            c.checked_in_at
     FROM tornei_squadre ts
     JOIN squadre s ON s.idSquadra = ts.idSquadra
     LEFT JOIN checkins c ON c.idTorneo = ts.idTorneo AND c.idSquadra = ts.idSquadra
     WHERE ts.idTorneo = :id
     ORDER BY checked_in DESC, ts.seed ASC, s.nomeSquadra ASC"
);
$stm3->execute([':id' => $id]);
$allTeams = $stm3->fetchAll(\PDO::FETCH_ASSOC);

$checkedInCount = count(array_filter($allTeams, fn($t) => $t['checked_in']));
$totalCount     = count($allTeams);

$pageTitle = 'Check-in — ' . htmlspecialchars($tournament['nomeTorneo'], ENT_QUOTES) . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>
<link rel="stylesheet" href="/assets/css/bracket.css">

<main class="page-main">
<div class="container" style="max-width:760px;">

    <a href="/bracket.php?id=<?= $id ?>" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:32px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        Bracket
    </a>

    <?php if ($msg === 'checked_in'): ?>
    <div style="background:rgba(0,232,120,0.08);border:1px solid rgba(0,232,120,0.3);border-radius:var(--radius);padding:12px 20px;margin-bottom:24px;color:#00e878;font-size:14px;font-weight:600;">
        ✓ Your team is checked in!
    </div>
    <?php elseif ($error): ?>
    <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <!-- Header -->
    <div style="margin-bottom:32px;" class="reveal">
        <span class="section-label">Tournament Check-in</span>
        <h1 style="font-size:clamp(22px,3.5vw,34px);font-weight:800;letter-spacing:-0.8px;margin-bottom:8px;">
            <?= htmlspecialchars($tournament['nomeTorneo'], ENT_QUOTES) ?>
        </h1>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
            <span style="font-size:14px;color:var(--text-secondary);">
                <?= $checkedInCount ?> / <?= $totalCount ?> teams checked in
            </span>
            <?php if ($tournament['status'] === 'checkin'): ?>
            <span class="badge badge-green" style="display:inline-flex;align-items:center;gap:4px;">
                <span class="live-dot"></span> CHECK-IN OPEN
            </span>
            <?php elseif ($tournament['status'] === 'registration'): ?>
            <span class="badge badge-gray">Registration Phase</span>
            <?php elseif ($tournament['status'] === 'live'): ?>
            <span class="badge badge-blue">Tournament Live</span>
            <?php endif; ?>
        </div>
        <!-- Progress bar -->
        <?php if ($totalCount > 0): ?>
        <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;max-width:400px;">
            <div style="height:100%;border-radius:3px;background:var(--accent-blue);width:<?= round($checkedInCount / $totalCount * 100) ?>%;transition:width 0.5s var(--ease-out);"></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- My teams check-in -->
    <?php if ($loggedIn && !empty($myTeams)): ?>
    <div class="reveal" style="margin-bottom:32px;">
        <span class="section-label">Your Teams</span>
        <?php foreach ($myTeams as $mt): ?>
        <div class="checkin-card <?= $mt['already_checked_in'] ? 'checked' : '' ?>">
            <div>
                <div style="font-family:var(--font-display);font-size:18px;font-weight:700;margin-bottom:4px;">
                    <?= htmlspecialchars($mt['nomeSquadra'], ENT_QUOTES) ?>
                    <?php if ($mt['is_captain']): ?>
                    <span class="badge badge-blue" style="margin-left:8px;font-size:10px;">Captain</span>
                    <?php endif; ?>
                </div>
                <?php if ($mt['already_checked_in']): ?>
                <span style="color:#00e878;font-size:13px;font-weight:600;">✓ Checked in for this tournament</span>
                <?php else: ?>
                <span style="color:var(--text-secondary);font-size:13px;">Not yet checked in</span>
                <?php endif; ?>
            </div>
            <?php if (!$mt['already_checked_in'] && in_array($tournament['status'], ['registration', 'checkin'])): ?>
            <form method="POST">
                <input type="hidden" name="idSquadra" value="<?= (int)$mt['idSquadra'] ?>">
                <button type="submit" class="btn-primary">Check In</button>
            </form>
            <?php elseif ($mt['already_checked_in']): ?>
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(0,232,120,0.15);border:1px solid rgba(0,232,120,0.4);display:flex;align-items:center;justify-content:center;color:#00e878;font-size:18px;">
                ✓
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php elseif (!$loggedIn): ?>
    <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;text-align:center;margin-bottom:32px;" class="reveal">
        <p style="font-size:15px;margin-bottom:16px;color:var(--text-secondary);">Sign in to check in your team for this tournament.</p>
        <a href="/login.php" class="btn-primary">Sign In</a>
    </div>
    <?php endif; ?>

    <!-- All teams list -->
    <div class="reveal">
        <span class="section-label">All Teams</span>
        <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Check-in Status</h2>
        <?php foreach ($allTeams as $t): ?>
        <div class="checkin-card <?= $t['checked_in'] ? 'checked' : '' ?>" style="padding:20px 24px;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,rgba(0,212,255,0.15),rgba(102,126,234,0.15));display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:13px;color:var(--accent-blue);">
                    <?= strtoupper(substr($t['nomeSquadra'], 0, 2)) ?>
                </div>
                <div>
                    <div style="font-weight:700;font-size:15px;"><?= htmlspecialchars($t['nomeSquadra'], ENT_QUOTES) ?></div>
                    <?php if ($t['seed']): ?>
                    <div style="font-size:12px;color:var(--text-secondary);">Seed #<?= (int)$t['seed'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div style="text-align:right;">
                <?php if ($t['checked_in']): ?>
                <span style="color:#00e878;font-size:13px;font-weight:600;">✓ Checked In</span>
                <?php if ($t['checked_in_at']): ?>
                <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;"><?= date('H:i', strtotime($t['checked_in_at'])) ?></div>
                <?php endif; ?>
                <?php else: ?>
                <span style="color:var(--text-secondary);font-size:13px;">Pending</span>
                <?php endif; ?>
                <?php if ($isAdmin && !$t['checked_in'] && in_array($tournament['status'], ['registration','checkin'])): ?>
                <form method="POST" style="margin-top:6px;">
                    <input type="hidden" name="idSquadra" value="<?= (int)$t['idSquadra'] ?>">
                    <button type="submit" class="btn-secondary" style="padding:4px 10px;font-size:11px;">Force Check-in</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
