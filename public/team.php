<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: /');
    exit;
}

// Team info + sponsor
$stm = $pdo->prepare(
    "SELECT s.*, sp.nomeAzienda, sp.emailResponsabile
     FROM squadre s
     LEFT JOIN sponsor sp ON sp.idSponsor = s.idSponsor
     WHERE s.idSquadra = :id"
);
$stm->execute([':id' => $id]);
$team = $stm->fetch(PDO::FETCH_ASSOC);
if (!$team) {
    header('Location: /');
    exit;
}

// Members + their roles
$stm2 = $pdo->prepare(
    "SELECT m.idMembro, m.nickname,
            u.nome, u.cognome,
            GROUP_CONCAT(r.nomeRuolo ORDER BY r.nomeRuolo SEPARATOR ', ') AS ruoli
     FROM membri m
     LEFT JOIN utenti u    ON u.idUtente  = m.idUtente
     LEFT JOIN membri_ruoli mr ON mr.idMembro = m.idMembro
     LEFT JOIN ruoli r     ON r.idRuolo   = mr.idRuolo
     WHERE m.idSquadra = :id
     GROUP BY m.idMembro, m.nickname, u.nome, u.cognome
     ORDER BY m.nickname"
);
$stm2->execute([':id' => $id]);
$members = $stm2->fetchAll(PDO::FETCH_ASSOC);

// Tournament history
$stm3 = $pdo->prepare(
    "SELECT t.nomeTorneo, t.montePremi, t.giornoSvolgimento,
            g.nomeGioco,
            e.nome AS eventName, e.idEvento
     FROM tornei_squadre ts
     JOIN tornei t  ON t.idTorneo = ts.idTorneo
     JOIN evento e  ON e.idEvento = t.idEvento
     LEFT JOIN giochi g ON g.idGioco = t.idGioco
     WHERE ts.idSquadra = :id
     ORDER BY t.giornoSvolgimento DESC
     LIMIT 20"
);
$stm3->execute([':id' => $id]);
$history = $stm3->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = htmlspecialchars($team['nomeSquadra'], ENT_QUOTES) . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
<div class="container">

    <!-- Back -->
    <a href="/#events" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:32px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        Back
    </a>

    <!-- Team Header -->
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:40px;margin-bottom:40px;">
        <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
            <div style="width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg,rgba(0,212,255,0.2),rgba(102,126,234,0.3));border:1px solid rgba(0,212,255,0.3);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:24px;color:var(--accent-blue);flex-shrink:0;">
                <?= strtoupper(substr($team['nomeSquadra'], 0, 2)) ?>
            </div>
            <div>
                <h1 style="font-size:clamp(22px,4vw,36px);font-weight:800;letter-spacing:-1px;margin-bottom:6px;">
                    <?= htmlspecialchars($team['nomeSquadra'], ENT_QUOTES) ?>
                </h1>
                <div style="display:flex;flex-wrap:wrap;gap:12px;color:var(--text-secondary);font-size:14px;align-items:center;">
                    <?php if ($team['nomeAzienda']): ?>
                    <span style="display:flex;align-items:center;gap:6px;color:var(--accent-blue);font-weight:600;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 14V6l6-4 6 4v8H10V9H6v5H2z"/></svg>
                        <?= htmlspecialchars($team['nomeAzienda'], ENT_QUOTES) ?>
                    </span>
                    <?php else: ?>
                    <span style="color:var(--text-secondary);">Independent</span>
                    <?php endif; ?>
                    <span style="display:flex;align-items:center;gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1a7 7 0 1 1 0 14A7 7 0 0 1 8 1z"/><path d="M8 4.5v4l2.5 2.5"/></svg>
                        <?= count($members) ?> member<?= count($members) != 1 ? 's' : '' ?>
                    </span>
                    <?php if (!empty($history)): ?>
                    <span style="display:flex;align-items:center;gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2l1.5 3 3.5.5-2.5 2.5.5 3.5L8 10l-3 1.5.5-3.5L3 5.5l3.5-.5z"/></svg>
                        <?= count($history) ?> tournament<?= count($history) != 1 ? 's' : '' ?> played
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;" class="reveal">

        <!-- Roster -->
        <div>
            <div style="margin-bottom:20px;">
                <span class="section-label">Squad</span>
                <h2 style="font-size:20px;font-weight:700;letter-spacing:-0.4px;">Roster</h2>
            </div>
            <?php if (empty($members)): ?>
            <p style="color:var(--text-secondary);">No members registered yet.</p>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($members as $m): ?>
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius);padding:14px 18px;display:flex;align-items:center;gap:14px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.2);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:12px;color:var(--accent-blue);flex-shrink:0;">
                        <?= strtoupper(substr($m['nickname'], 0, 2)) ?>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($m['nickname'], ENT_QUOTES) ?></div>
                        <?php if ($m['ruoli']): ?>
                        <div style="font-size:11px;color:var(--text-secondary);"><?= htmlspecialchars($m['ruoli'], ENT_QUOTES) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tournament History -->
        <div>
            <div style="margin-bottom:20px;">
                <span class="section-label">Record</span>
                <h2 style="font-size:20px;font-weight:700;letter-spacing:-0.4px;">Tournament History</h2>
            </div>
            <?php if (empty($history)): ?>
            <p style="color:var(--text-secondary);">No tournaments played yet.</p>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($history as $h): ?>
                <a href="/event.php?id=<?= (int)$h['idEvento'] ?>" style="text-decoration:none;">
                    <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius);padding:14px 18px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(0,212,255,0.4)'" onmouseout="this.style.borderColor='var(--border)'">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                            <div>
                                <div style="font-weight:600;font-size:13px;color:var(--text-primary);"><?= htmlspecialchars($h['nomeTorneo'], ENT_QUOTES) ?></div>
                                <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">
                                    <?= htmlspecialchars($h['eventName'], ENT_QUOTES) ?>
                                    <?php if ($h['nomeGioco']): ?> · <?= htmlspecialchars($h['nomeGioco'], ENT_QUOTES) ?><?php endif; ?>
                                </div>
                            </div>
                            <?php if ($h['montePremi']): ?>
                            <span style="font-family:var(--font-display);font-weight:700;color:var(--accent-blue);font-size:13px;">€<?= number_format((float)$h['montePremi'], 0, '.', ',') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
