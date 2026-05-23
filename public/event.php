<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: /');
    exit;
}

// Fetch event
$stm = $pdo->prepare(
    "SELECT e.*,
            (SELECT SUM(t.montePremi) FROM tornei t WHERE t.idEvento = e.idEvento) AS totalPrize
     FROM evento e
     WHERE e.idEvento = :id"
);
$stm->execute([':id' => $id]);
$event = $stm->fetch(PDO::FETCH_ASSOC);
if (!$event) {
    header('Location: /');
    exit;
}

// Fetch tournaments for this event
$stm2 = $pdo->prepare(
    "SELECT t.idTorneo, t.nomeTorneo, t.montePremi, t.giornoSvolgimento,
            g.nomeGioco,
            (SELECT COUNT(*) FROM tornei_squadre ts WHERE ts.idTorneo = t.idTorneo) AS teamCount
     FROM tornei t
     LEFT JOIN giochi g ON g.idGioco = t.idGioco
     WHERE t.idEvento = :id
     ORDER BY t.giornoSvolgimento ASC"
);
$stm2->execute([':id' => $id]);
$tournaments = $stm2->fetchAll(PDO::FETCH_ASSOC);

// Fetch top teams across all tournaments for this event
$stm3 = $pdo->prepare(
    "SELECT DISTINCT s.idSquadra, s.nomeSquadra, sp.nomeAzienda, g.nomeGioco
     FROM squadre s
     JOIN tornei_squadre ts ON s.idSquadra = ts.idSquadra
     JOIN tornei t          ON ts.idTorneo = t.idTorneo
     LEFT JOIN giochi g     ON g.idGioco = t.idGioco
     LEFT JOIN sponsor sp   ON sp.idSponsor = s.idSponsor
     WHERE t.idEvento = :id
     ORDER BY s.nomeSquadra
     LIMIT 24"
);
$stm3->execute([':id' => $id]);
$teams = $stm3->fetchAll(PDO::FETCH_ASSOC);

$isLAN   = !empty(trim((string)($event['citta'] ?? '')));
$location = trim(implode(', ', array_filter([$event['citta'] ?? '', $event['paese'] ?? ''])));
$dateStart = $event['dataInizio'] ? date('M j', strtotime($event['dataInizio'])) : '';
$dateEnd   = $event['dataFine']   ? date('M j, Y', strtotime($event['dataFine'])) : '';
$totalPrize = $event['totalPrize'] ? '€' . number_format((float)$event['totalPrize'], 0, '.', ',') : null;

$pageTitle = htmlspecialchars($event['nome'], ENT_QUOTES) . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
<div class="container">

    <!-- Back -->
    <a href="/#events" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:32px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        <?= t('nav_events') ?>
    </a>

    <!-- Event Header -->
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:40px;margin-bottom:40px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:24px;">
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                    <span class="badge <?= $isLAN ? 'badge-blue' : 'badge-green' ?>" style="font-size:12px;padding:4px 12px;">
                        <?= $isLAN ? t('filter_lan') : t('filter_online') ?>
                    </span>
                    <?php if ($totalPrize): ?>
                    <span style="background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.3);border-radius:20px;padding:4px 14px;font-size:13px;font-weight:700;color:var(--accent-blue);">
                        <?= $totalPrize ?> <?= t('event_prize_pool') ?>
                    </span>
                    <?php endif; ?>
                </div>
                <h1 style="font-size:clamp(24px,4vw,40px);font-weight:800;letter-spacing:-1px;margin-bottom:12px;line-height:1.1;">
                    <?= htmlspecialchars($event['nome'], ENT_QUOTES) ?>
                </h1>
                <div style="display:flex;flex-wrap:wrap;gap:20px;color:var(--text-secondary);font-size:14px;">
                    <?php if ($dateStart): ?>
                    <span style="display:flex;align-items:center;gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="14" height="12" rx="2"/><path d="M5 1v4M11 1v4M1 7h14"/></svg>
                        <?= $dateStart ?><?= $dateEnd ? ' — ' . $dateEnd : '' ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($location): ?>
                    <span style="display:flex;align-items:center;gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="6" r="3"/><path d="M8 1C5.24 1 3 3.24 3 6c0 4 5 9 5 9s5-5 5-9c0-2.76-2.24-5-5-5z"/></svg>
                        <?= htmlspecialchars($location, ENT_QUOTES) ?>
                    </span>
                    <?php endif; ?>
                    <span style="display:flex;align-items:center;gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1a7 7 0 1 1 0 14A7 7 0 0 1 8 1z"/><path d="M8 4.5v4l2.5 2.5"/></svg>
                        <?= (int)$event['nPosti'] ?> <?= t('event_slots') ?>
                    </span>
                </div>
            </div>
            <?php if (isset($_SESSION['email'])): ?>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <?php if (!empty($tournaments)): ?>
                <a href="/signTeam.php?id=<?= (int)$tournaments[0]['idTorneo'] ?>" class="btn-primary" style="white-space:nowrap;">
                    Register Team →
                </a>
                <?php endif; ?>
                <a href="/dashboard.php?id=<?= $id ?>" class="btn-secondary">
                    Management View
                </a>
            </div>
            <?php else: ?>
            <a href="/register.php" class="btn-primary" style="white-space:nowrap;">
                <?= t('nav_register') ?> →
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tournaments -->
    <?php if (!empty($tournaments)): ?>
    <div class="reveal" style="margin-bottom:48px;">
        <div style="margin-bottom:24px;">
            <span class="section-label">Competition Layer</span>
            <h2 style="font-size:22px;font-weight:700;letter-spacing:-0.5px;">Tournaments</h2>
        </div>
        <div style="display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));">
            <?php foreach ($tournaments as $t): ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(0,212,255,0.4)'" onmouseout="this.style.borderColor='var(--border)'">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                    <span class="badge badge-blue" style="font-size:11px;"><?= htmlspecialchars($t['nomeGioco'] ?? '—', ENT_QUOTES) ?></span>
                    <span style="font-size:12px;color:var(--text-secondary);"><?= htmlspecialchars($t['giornoSvolgimento'] ?? '', ENT_QUOTES) ?></span>
                </div>
                <h3 style="font-family:var(--font-display);font-size:17px;font-weight:700;margin-bottom:12px;letter-spacing:-0.3px;">
                    <?= htmlspecialchars($t['nomeTorneo'], ENT_QUOTES) ?>
                </h3>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <?php if ($t['montePremi']): ?>
                        <span style="font-family:var(--font-display);font-weight:800;color:var(--accent-blue);font-size:18px;">€<?= number_format((float)$t['montePremi'], 0, '.', ',') ?></span>
                        <span style="font-size:11px;color:var(--text-secondary);margin-left:4px;"><?= t('event_prize_pool') ?></span>
                        <?php else: ?>
                        <span style="color:var(--text-secondary);font-size:13px;"><?= t('event_tba') ?></span>
                        <?php endif; ?>
                    </div>
                    <span style="font-size:12px;color:var(--text-secondary);">
                        <?= (int)$t['teamCount'] ?> team<?= $t['teamCount'] != 1 ? 's' : '' ?>
                    </span>
                </div>
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="/bracket.php?id=<?= (int)$t['idTorneo'] ?>" class="btn-secondary" style="flex:1;text-align:center;padding:7px;font-size:12px;min-width:80px;">
                        Bracket
                    </a>
                    <a href="/viewTeam.php?id=<?= (int)$t['idTorneo'] ?>" class="btn-secondary" style="flex:1;text-align:center;padding:7px;font-size:12px;min-width:80px;">
                        Rosters
                    </a>
                    <?php if (isset($_SESSION['email'])): ?>
                    <a href="/signTeam.php?id=<?= (int)$t['idTorneo'] ?>" class="btn-primary" style="flex:1;text-align:center;padding:7px;font-size:12px;min-width:60px;">
                        Enter
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Registered Teams -->
    <?php if (!empty($teams)): ?>
    <div class="reveal" style="margin-bottom:48px;">
        <div style="margin-bottom:24px;">
            <span class="section-label">Participants</span>
            <h2 style="font-size:22px;font-weight:700;letter-spacing:-0.5px;">Registered Organisations</h2>
        </div>
        <div style="display:grid;gap:12px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
            <?php foreach ($teams as $team): ?>
            <a href="/team.php?id=<?= (int)$team['idSquadra'] ?>" style="text-decoration:none;">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius);padding:16px 20px;display:flex;align-items:center;gap:14px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(0,212,255,0.4)'" onmouseout="this.style.borderColor='var(--border)'">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,rgba(0,212,255,0.2),rgba(102,126,234,0.2));border:1px solid rgba(0,212,255,0.3);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:13px;color:var(--accent-blue);flex-shrink:0;">
                        <?= htmlspecialchars(strtoupper(substr($team['nomeSquadra'], 0, 2)), ENT_QUOTES) ?>
                    </div>
                    <div style="min-width:0;">
                        <div style="font-weight:600;font-size:14px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($team['nomeSquadra'], ENT_QUOTES) ?></div>
                        <?php if ($team['nomeGioco']): ?>
                        <div style="font-size:11px;color:var(--text-secondary);"><?= htmlspecialchars($team['nomeGioco'], ENT_QUOTES) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- CTA if not logged in -->
    <?php if (!isset($_SESSION['email'])): ?>
    <div class="reveal" style="background:linear-gradient(135deg,rgba(0,212,255,0.05),rgba(102,126,234,0.05));border:1px solid rgba(0,212,255,0.2);border-radius:var(--radius-lg);padding:48px;text-align:center;margin-bottom:48px;">
        <span class="section-label">Join the Competition</span>
        <h2 style="font-size:28px;font-weight:800;letter-spacing:-0.8px;margin-bottom:12px;">Ready to compete?</h2>
        <p style="color:var(--text-secondary);margin-bottom:32px;max-width:480px;margin-left:auto;margin-right:auto;">Create a free account to register your team, track standings, and participate in professional tournaments.</p>
        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <a href="/register.php" class="btn-primary"><?= t('nav_register') ?></a>
            <a href="/login.php" class="btn-secondary"><?= t('nav_signin') ?></a>
        </div>
    </div>
    <?php endif; ?>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
