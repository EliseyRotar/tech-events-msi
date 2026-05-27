<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
\App\Auth::requireLogin();

$userId = (int)$_SESSION['id'];

// Fetch full user data
$stm = $pdo->prepare("SELECT idUtente, nome, cognome, username, email, dataNascita, isAdmin FROM utenti WHERE idUtente = :id");
$stm->execute([':id' => $userId]);
$user = $stm->fetch(PDO::FETCH_ASSOC);

// Fetch teams the user belongs to
$stm2 = $pdo->prepare(
    "SELECT s.idSquadra, s.nomeSquadra, m.nickname,
            (SELECT COUNT(*) FROM membri m2 WHERE m2.idSquadra = s.idSquadra) AS memberCount,
            (SELECT COUNT(*) FROM tornei_squadre ts WHERE ts.idSquadra = s.idSquadra) AS tournamentCount,
            sp.nomeAzienda
     FROM membri m
     JOIN squadre s  ON s.idSquadra = m.idSquadra
     LEFT JOIN sponsor sp ON sp.idSponsor = s.idSponsor
     WHERE m.idUtente = :id
     ORDER BY s.nomeSquadra"
);
$stm2->execute([':id' => $userId]);
$teams = $stm2->fetchAll(PDO::FETCH_ASSOC);

// Fetch tournament history via user's teams
$teamIds = array_column($teams, 'idSquadra');
$history = [];
if (!empty($teamIds)) {
    $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
    $stm3 = $pdo->prepare(
        "SELECT t.idTorneo, t.nomeTorneo, t.montePremi, t.giornoSvolgimento,
                g.nomeGioco, e.nome AS eventName, e.idEvento,
                s.nomeSquadra, s.idSquadra
         FROM tornei_squadre ts
         JOIN tornei t  ON t.idTorneo = ts.idTorneo
         JOIN evento e  ON e.idEvento = t.idEvento
         JOIN squadre s ON s.idSquadra = ts.idSquadra
         LEFT JOIN giochi g ON g.idGioco = t.idGioco
         WHERE ts.idSquadra IN ($placeholders)
         ORDER BY t.giornoSvolgimento DESC
         LIMIT 20"
    );
    $stm3->execute($teamIds);
    $history = $stm3->fetchAll(PDO::FETCH_ASSOC);
}

// Count stats
$matchesPlayed = 0;
try {
    $stm4 = $pdo->prepare(
        "SELECT COUNT(*) FROM matches m
         JOIN squadre s ON (s.idSquadra = m.idSquadra1 OR s.idSquadra = m.idSquadra2)
         JOIN membri mb ON mb.idSquadra = s.idSquadra
         WHERE mb.idUtente = :id AND m.status = 'completed'"
    );
    $stm4->execute([':id' => $userId]);
    $matchesPlayed = (int)$stm4->fetchColumn();
} catch (\PDOException $e) {}

$eventsCount = count(array_unique(array_column($history, 'idEvento')));
$isAdmin     = (int)($user['isAdmin'] ?? 0) === 1;
$initials    = strtoupper(substr($user['nome'], 0, 1) . substr($user['cognome'], 0, 1));
$displayName = $user['username'] ? '@' . $user['username'] : $user['nome'] . ' ' . $user['cognome'];

$pageTitle = t('profile_title') . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
<div class="container">

    <!-- Profile Header Card -->
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:40px;margin-bottom:32px;">
        <div style="display:flex;align-items:center;gap:28px;flex-wrap:wrap;">

            <!-- Avatar -->
            <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,rgba(0,212,255,0.25),rgba(102,126,234,0.35));border:2px solid rgba(0,212,255,0.4);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:28px;color:var(--accent-blue);flex-shrink:0;letter-spacing:-1px;">
                <?= htmlspecialchars($initials, ENT_QUOTES) ?>
            </div>

            <!-- Info -->
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
                    <h1 style="font-size:clamp(20px,3vw,30px);font-weight:800;letter-spacing:-0.8px;margin:0;">
                        <?= htmlspecialchars($user['nome'] . ' ' . $user['cognome'], ENT_QUOTES) ?>
                    </h1>
                    <span class="badge <?= $isAdmin ? 'badge-blue' : 'badge-green' ?>">
                        <?= $isAdmin ? t('profile_role_admin') : t('profile_role_player') ?>
                    </span>
                </div>
                <?php if ($user['username']): ?>
                <div style="font-family:var(--font-display);font-size:15px;color:var(--accent-blue);font-weight:600;margin-bottom:8px;">
                    @<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>
                </div>
                <?php endif; ?>
                <div style="font-size:13px;color:var(--text-secondary);">
                    <?= htmlspecialchars($user['email'], ENT_QUOTES) ?>
                </div>
            </div>

            <!-- Quick actions -->
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <?php if ($isAdmin): ?>
                <a href="/dashboard.php" class="btn-primary" style="white-space:nowrap;">Dashboard →</a>
                <?php endif; ?>
                <a href="/addTeam.php" class="btn-secondary" style="white-space:nowrap;">+ <?= t('profile_join_team') ?></a>
            </div>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="reveal" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:48px;">
        <?php
        $stats = [
            [count($teams),    t('profile_stat_teams')],
            [$eventsCount,     t('profile_stat_events')],
            [$matchesPlayed,   t('profile_stat_matches')],
        ];
        foreach ($stats as [$val, $lbl]):
        ?>
        <div style="background:var(--bg-secondary);padding:24px;text-align:center;">
            <div style="font-family:var(--font-display);font-size:36px;font-weight:800;color:var(--accent-blue);letter-spacing:-1.5px;line-height:1;">
                <?= $val ?>
            </div>
            <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-top:6px;">
                <?= $lbl ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;" class="reveal">

        <!-- Teams -->
        <div>
            <div style="margin-bottom:20px;">
                <span class="section-label"><?= t('profile_label') ?></span>
                <h2 style="font-size:20px;font-weight:700;letter-spacing:-0.4px;"><?= t('profile_your_teams') ?></h2>
            </div>

            <?php if (empty($teams)): ?>
            <div style="background:var(--bg-secondary);border:1px dashed var(--border);border-radius:var(--radius-lg);padding:40px;text-align:center;">
                <div style="font-size:40px;margin-bottom:12px;">🎮</div>
                <p style="font-family:var(--font-display);font-weight:700;margin-bottom:6px;"><?= t('profile_no_teams') ?></p>
                <a href="/addTeam.php" class="btn-primary" style="margin-top:16px;display:inline-flex;">
                    + <?= t('profile_join_team') ?>
                </a>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($teams as $team): ?>
                <a href="/team.php?id=<?= (int)$team['idSquadra'] ?>" style="text-decoration:none;">
                    <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px 20px;display:flex;align-items:center;gap:14px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(0,212,255,0.4)'" onmouseout="this.style.borderColor='var(--border)'">
                        <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,rgba(0,212,255,0.15),rgba(102,126,234,0.2));border:1px solid rgba(0,212,255,0.25);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:14px;color:var(--accent-blue);flex-shrink:0;">
                            <?= htmlspecialchars(strtoupper(substr($team['nomeSquadra'], 0, 2)), ENT_QUOTES) ?>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:14px;margin-bottom:2px;"><?= htmlspecialchars($team['nomeSquadra'], ENT_QUOTES) ?></div>
                            <div style="font-size:11px;color:var(--text-secondary);">
                                <?= (int)$team['memberCount'] ?> <?= t('profile_members') ?>
                                <?php if ($team['tournamentCount'] > 0): ?>
                                &nbsp;·&nbsp; <?= (int)$team['tournamentCount'] ?> <?= (int)$team['tournamentCount'] === 1 ? t('profile_tournaments') : t('profile_tournaments_pl') ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($team['nickname']): ?>
                            <div style="font-size:11px;color:var(--accent-blue);margin-top:2px;">
                                as <?= htmlspecialchars($team['nickname'], ENT_QUOTES) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="var(--text-secondary)" stroke-width="1.8" stroke-linecap="round"><path d="M5 3l5 5-5 5"/></svg>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tournament History -->
        <div>
            <div style="margin-bottom:20px;">
                <span class="section-label">Record</span>
                <h2 style="font-size:20px;font-weight:700;letter-spacing:-0.4px;"><?= t('profile_history') ?></h2>
            </div>

            <?php if (empty($history)): ?>
            <div style="background:var(--bg-secondary);border:1px dashed var(--border);border-radius:var(--radius-lg);padding:40px;text-align:center;">
                <div style="font-size:40px;margin-bottom:12px;">🏆</div>
                <p style="font-family:var(--font-display);font-weight:700;margin-bottom:6px;"><?= t('profile_no_history') ?></p>
                <a href="/#events" class="btn-secondary" style="margin-top:16px;display:inline-flex;">
                    <?= t('profile_view_events') ?>
                </a>
            </div>
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
                                    &nbsp;·&nbsp; <?= htmlspecialchars($h['nomeSquadra'], ENT_QUOTES) ?>
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
