<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$teams       = [];
$globalStats = ['totalTeams' => 0, 'totalMatches' => 0, 'completedTournaments' => 0, 'totalPrize' => 0];
$dbReady     = false;

try {
    $stm = $pdo->prepare(
        "SELECT
            s.idSquadra,
            s.nomeSquadra,
            sp.nomeAzienda AS sponsorName,
            COUNT(DISTINCT ts.idTorneo) AS tournamentsEntered,
            COALESCE(SUM(CASE WHEN ts.placement = 1 THEN 1 ELSE 0 END), 0) AS wins,
            COALESCE(SUM(CASE WHEN ts.placement = 2 THEN 1 ELSE 0 END), 0) AS runnerUps,
            (SELECT COUNT(*) FROM matches m
             WHERE (m.idSquadra1 = s.idSquadra OR m.idSquadra2 = s.idSquadra)
             AND m.status = 'completed') AS matchesPlayed,
            (SELECT COUNT(*) FROM matches m
             WHERE m.idVincitore = s.idSquadra
             AND m.status = 'completed') AS matchWins,
            COALESCE(SUM(t.montePremi * CASE WHEN ts.placement = 1 THEN 1 ELSE 0 END), 0) AS prizeEarned
         FROM squadre s
         LEFT JOIN tornei_squadre ts ON ts.idSquadra = s.idSquadra
         LEFT JOIN tornei t          ON t.idTorneo = ts.idTorneo
         LEFT JOIN sponsor sp        ON sp.idSponsor = s.idSponsor
         GROUP BY s.idSquadra, s.nomeSquadra, sp.nomeAzienda
         HAVING matchesPlayed > 0 OR tournamentsEntered > 0
         ORDER BY wins DESC, matchWins DESC, tournamentsEntered DESC"
    );
    $stm->execute();
    $teams = $stm->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($teams as &$t) {
        $t['winRate'] = $t['matchesPlayed'] > 0
            ? round($t['matchWins'] / $t['matchesPlayed'] * 100)
            : 0;
    }
    unset($t);

    usort($teams, function($a, $b) {
        if ($b['wins'] !== $a['wins']) return $b['wins'] <=> $a['wins'];
        if ($b['matchWins'] !== $a['matchWins']) return $b['matchWins'] <=> $a['matchWins'];
        return $b['winRate'] <=> $a['winRate'];
    });

    $statStm = $pdo->prepare(
        "SELECT
            (SELECT COUNT(*) FROM squadre) AS totalTeams,
            (SELECT COUNT(*) FROM matches WHERE status = 'completed') AS totalMatches,
            (SELECT COUNT(*) FROM tornei WHERE status = 'completed') AS completedTournaments,
            (SELECT COALESCE(SUM(montePremi), 0) FROM tornei) AS totalPrize"
    );
    $statStm->execute();
    $globalStats = $statStm->fetch(\PDO::FETCH_ASSOC);
    $dbReady = true;
} catch (\PDOException $e) {
    // Migration 05 not yet applied — show empty state
    $dbReady = false;
}

$pageTitle = 'Leaderboard — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>
<link rel="stylesheet" href="/assets/css/bracket.css">

<main class="page-main">
<div class="container">

    <!-- Header -->
    <div class="reveal" style="margin-bottom:48px;">
        <span class="section-label"><?= t('lb_label') ?></span>
        <h1 style="font-size:clamp(28px,4vw,48px);font-weight:800;letter-spacing:-1.5px;margin-bottom:12px;">
            <?= t('lb_title') ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:15px;max-width:500px;">
            <?= t('lb_sub') ?>
        </p>
    </div>

    <?php if (!$dbReady): ?>
    <div style="background:rgba(0,212,255,0.05);border:1px solid rgba(0,212,255,0.2);border-radius:var(--radius-lg);padding:48px;text-align:center;margin-bottom:48px;" class="reveal">
        <div style="font-size:48px;margin-bottom:16px;">⚙️</div>
        <p style="font-family:var(--font-display);font-size:20px;font-weight:700;margin-bottom:8px;"><?= t('lb_setup_title') ?></p>
        <p style="color:var(--text-secondary);max-width:420px;margin:0 auto;">
            <?= t('lb_setup_sub') ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Global stats bar -->
    <div class="reveal" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1px;background:var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:48px;">
        <?php
        $gs = [
            ['label' => t('lb_stat_teams'),       'value' => number_format((int)$globalStats['totalTeams'])],
            ['label' => t('lb_stat_matches'),      'value' => number_format((int)$globalStats['totalMatches'])],
            ['label' => t('lb_stat_tournaments'),  'value' => number_format((int)$globalStats['completedTournaments'])],
            ['label' => t('lb_stat_prize'),        'value' => '€' . number_format((float)$globalStats['totalPrize'], 0, '.', ',')],
        ];
        foreach ($gs as $stat):
        ?>
        <div style="background:var(--bg-secondary);padding:24px;">
            <div style="font-family:var(--font-display);font-size:28px;font-weight:800;color:var(--accent-blue);letter-spacing:-1px;"><?= $stat['value'] ?></div>
            <div style="font-size:12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-top:4px;"><?= $stat['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Leaderboard table -->
    <?php if (empty($teams)): ?>
    <div style="background:var(--bg-secondary);border:1px dashed var(--border);border-radius:var(--radius-lg);padding:80px;text-align:center;" class="reveal">
        <p style="font-family:var(--font-display);font-size:20px;font-weight:700;margin-bottom:8px;"><?= t('lb_empty') ?></p>
        <p style="color:var(--text-secondary);"><?= t('lb_empty_sub') ?></p>
    </div>
    <?php else: ?>
    <div class="data-table-wrap reveal">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:50px;"><?= t('lb_rank') ?></th>
                    <th><?= t('lb_org') ?></th>
                    <th style="text-align:center"><?= t('lb_won') ?></th>
                    <th style="text-align:center"><?= t('lb_match_wins') ?></th>
                    <th style="text-align:center"><?= t('lb_matches') ?></th>
                    <th style="text-align:center"><?= t('lb_win_rate') ?></th>
                    <th style="text-align:right"><?= t('lb_prize') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $i => $t): ?>
                <?php $rank = $i + 1; ?>
                <tr>
                    <td>
                        <span style="font-family:var(--font-display);font-size:18px;font-weight:800;
                            <?= $rank === 1 ? 'color:#FFD700;' : ($rank === 2 ? 'color:#C0C0C0;' : ($rank === 3 ? 'color:#CD7F32;' : 'color:var(--text-secondary);')) ?>">
                            <?= $rank <= 3 ? ['🥇','🥈','🥉'][$rank - 1] : '#' . $rank ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,rgba(0,212,255,0.15),rgba(102,126,234,0.15));border:1px solid rgba(0,212,255,0.2);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:13px;color:var(--accent-blue);flex-shrink:0;">
                                <?= strtoupper(substr($t['nomeSquadra'], 0, 2)) ?>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:15px;"><?= htmlspecialchars($t['nomeSquadra'], ENT_QUOTES) ?></div>
                                <?php if ($t['sponsorName']): ?>
                                <div style="font-size:11px;color:var(--text-secondary);"><?= htmlspecialchars($t['sponsorName'], ENT_QUOTES) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($t['wins'] > 0): ?>
                        <span style="font-family:var(--font-display);font-weight:800;font-size:18px;color:#FFD700;"><?= $t['wins'] ?></span>
                        <?php else: ?>
                        <span style="color:var(--text-secondary);">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-family:var(--font-display);font-weight:700;font-size:16px;"><?= $t['matchWins'] ?></span>
                        <span style="color:var(--text-secondary);font-size:12px;margin-left:2px;">W</span>
                    </td>
                    <td style="text-align:center;color:var(--text-secondary);font-size:14px;">
                        <?= $t['matchesPlayed'] ?>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
                            <span class="lb-bar" style="width:<?= max(4, round($t['winRate'] / 100 * 80)) ?>px;"></span>
                            <span style="font-size:13px;font-weight:600;color:<?= $t['winRate'] >= 60 ? 'var(--success)' : ($t['winRate'] >= 40 ? 'var(--accent-blue)' : 'var(--text-secondary)') ?>;">
                                <?= $t['winRate'] ?>%
                            </span>
                        </div>
                    </td>
                    <td style="text-align:right;font-family:var(--font-display);font-weight:700;color:var(--accent-blue);">
                        <?= $t['prizeEarned'] > 0 ? '€' . number_format((float)$t['prizeEarned'], 0, '.', ',') : '—' ?>
                    </td>
                    <td>
                        <a href="/team.php?id=<?= (int)$t['idSquadra'] ?>" class="btn-secondary" style="padding:4px 10px;font-size:11px;white-space:nowrap;">
                            View →
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
