<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$isIt = (($_COOKIE['lang'] ?? 'it') === 'it');

// Live platform stats
$stats = ['events' => 0, 'teams' => 0, 'players' => 0, 'tournaments' => 0];
try {
    $stm = $pdo->query(
        "SELECT
            (SELECT COUNT(*) FROM evento)  AS events,
            (SELECT COUNT(*) FROM squadre) AS teams,
            (SELECT COUNT(*) FROM utenti)  AS players,
            (SELECT COUNT(*) FROM tornei)  AS tournaments"
    );
    $row = $stm->fetch(\PDO::FETCH_ASSOC);
    if ($row) $stats = $row;
} catch (\PDOException $e) {}

$pageTitle = $isIt
    ? 'Chi Siamo — Tech Dragons Events'
    : 'About — Tech Dragons Events';

require_once __DIR__ . '/../templates/layout/header.php';
?>
<link rel="stylesheet" href="/assets/css/bracket.css">

<main class="page-main">
<div class="container">

    <!-- Hero -->
    <div class="reveal" style="text-align:center;margin-bottom:72px;padding-top:8px;">
        <span class="section-label"><?= $isIt ? 'La Piattaforma' : 'The Platform' ?></span>
        <h1 style="font-size:clamp(36px,6vw,64px);font-weight:800;letter-spacing:-2px;margin-bottom:20px;line-height:1.1;">
            <?= $isIt ? 'Chi Siamo' : 'About Us' ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:17px;max-width:600px;margin:0 auto;line-height:1.7;">
            <?= $isIt
                ? 'Tech Dragons Events è la piattaforma di riferimento per l\'organizzazione e la gestione di tornei esports. Progettata per competizioni LAN e online, connette giocatori, team e organizzatori in un\'unica infrastruttura professionale.'
                : 'Tech Dragons Events is the go-to platform for organising and managing esports tournaments. Built for LAN and online competitions, it connects players, teams, and organisers in one professional infrastructure.' ?>
        </p>
    </div>

    <!-- Live stats -->
    <div class="reveal" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1px;background:var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:72px;">
        <?php
        $statItems = [
            ['value' => number_format((int)$stats['events']),      'label' => $isIt ? 'Eventi Ospitati'    : 'Events Hosted'],
            ['value' => number_format((int)$stats['tournaments']), 'label' => $isIt ? 'Tornei Organizzati' : 'Tournaments Run'],
            ['value' => number_format((int)$stats['teams']),       'label' => $isIt ? 'Team Registrati'    : 'Registered Teams'],
            ['value' => number_format((int)$stats['players']),     'label' => $isIt ? 'Giocatori Attivi'   : 'Active Players'],
        ];
        foreach ($statItems as $s):
        ?>
        <div style="background:var(--bg-secondary);padding:28px 24px;">
            <div style="font-family:var(--font-display);font-size:36px;font-weight:800;color:var(--accent-blue);letter-spacing:-1.5px;line-height:1;">
                <?= $s['value'] ?>
            </div>
            <div style="font-size:12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-top:6px;">
                <?= $s['label'] ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Mission -->
    <div class="reveal" style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;margin-bottom:80px;">
        <div>
            <span class="section-label"><?= $isIt ? 'La Nostra Missione' : 'Our Mission' ?></span>
            <h2 style="font-size:clamp(24px,3.5vw,38px);font-weight:800;letter-spacing:-1px;margin-bottom:20px;line-height:1.2;">
                <?= $isIt
                    ? 'Rendere la competizione esports accessibile a tutti'
                    : 'Making esports competition accessible to everyone' ?>
            </h2>
            <p style="color:var(--text-secondary);font-size:15px;line-height:1.75;margin-bottom:16px;">
                <?= $isIt
                    ? 'Abbiamo costruito Tech Dragons Events perché organizzare un torneo non dovrebbe richiedere settimane di spreadsheet, canali Discord caotici e fogli Google condivisi. Ogni campione merita un\'arena professionale — che si tratti di una LAN tra amici o di un evento con montepremi.'
                    : 'We built Tech Dragons Events because running a tournament should not require weeks of spreadsheets, chaotic Discord channels, and shared Google Sheets. Every champion deserves a professional arena — whether it\'s a LAN between friends or a prize-pool event.' ?>
            </p>
            <p style="color:var(--text-secondary);font-size:15px;line-height:1.75;">
                <?= $isIt
                    ? 'La piattaforma gestisce il ciclo completo: dalla creazione dell\'evento alla generazione del bracket, dal check-in in tempo reale alla pubblicazione automatica dei risultati. Gli organizzatori si concentrano sulla competizione — ci pensa Tech Dragons al resto.'
                    : 'The platform handles the full lifecycle: from event creation to bracket generation, from real-time check-in to automatic result publishing. Organisers focus on the competition — Tech Dragons handles everything else.' ?>
            </p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <?php
            $pillars = $isIt ? [
                ['icon'=>'🏆','title'=>'Tornei completi','desc'=>'Bracket single-elimination con seeding, bye automatici e avanzamento dei vincitori in tempo reale.'],
                ['icon'=>'🛡️','title'=>'Sicurezza RBAC','desc'=>'Controllo degli accessi basato sui ruoli con hashing Argon2ID. Admin, capitani e giocatori hanno permessi separati.'],
                ['icon'=>'📊','title'=>'Statistiche live','desc'=>'Classifica globale aggiornata dopo ogni match. Win rate, tornei vinti e montepremi guadagnati per ogni team.'],
                ['icon'=>'🔔','title'=>'Notifiche Discord','desc'=>'Webhook Discord per aggiornamenti automatici su risultati, inizio torneo e vincitore finale direttamente nel tuo server.'],
            ] : [
                ['icon'=>'🏆','title'=>'Full tournaments','desc'=>'Single-elimination brackets with seeding, automatic byes, and real-time winner advancement.'],
                ['icon'=>'🛡️','title'=>'RBAC security','desc'=>'Role-based access control with Argon2ID hashing. Admins, captains, and players have separate permission layers.'],
                ['icon'=>'📊','title'=>'Live statistics','desc'=>'Global leaderboard updated after every match. Win rate, tournament wins, and prize earnings for every team.'],
                ['icon'=>'🔔','title'=>'Discord alerts','desc'=>'Discord webhooks for automatic updates on results, tournament start, and final champion — straight to your server.'],
            ];
            foreach ($pillars as $p):
            ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;transition:border-color 0.2s;">
                <div style="font-size:24px;margin-bottom:10px;"><?= $p['icon'] ?></div>
                <div style="font-family:var(--font-display);font-size:14px;font-weight:700;margin-bottom:6px;"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></div>
                <div style="font-size:13px;color:var(--text-secondary);line-height:1.55;"><?= htmlspecialchars($p['desc'], ENT_QUOTES) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Who it's for -->
    <div class="reveal" style="margin-bottom:72px;">
        <span class="section-label"><?= $isIt ? 'Per Chi è Pensata' : 'Who It\'s For' ?></span>
        <h2 style="font-size:clamp(22px,3vw,34px);font-weight:800;letter-spacing:-0.8px;margin-bottom:32px;">
            <?= $isIt ? 'Una piattaforma, tre tipi di utenti' : 'One platform, three types of users' ?>
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;">
            <?php
            $audiences = $isIt ? [
                ['icon'=>'🎮','badge'=>'Giocatori','title'=>'Competitori','desc'=>'Crea un account, entra in un team e partecipa ai tornei con il tuo roster. Traccia le tue statistiche storiche nella classifica globale e scala i ranghi del competitive.','link'=>'/guide.php#tab-players','lbl'=>'Guida per giocatori →'],
                ['icon'=>'👑','badge'=>'Capitani','title'=>'Capi Team','desc'=>'Crea il tuo team, gestisci il roster, iscriviti ai tornei e coordina i check-in dei tuoi giocatori prima di ogni competizione.','link'=>'/guide.php#tab-captains','lbl'=>'Guida per capitani →'],
                ['icon'=>'⚙️','badge'=>'Admin','title'=>'Organizzatori','desc'=>'Crea eventi e tornei, assegna seed, genera bracket automatici, registra i punteggi e ricevi notifiche Discord in tempo reale. Zero spreadsheet.','link'=>'/guide.php#tab-admins','lbl'=>'Guida per organizzatori →'],
            ] : [
                ['icon'=>'🎮','badge'=>'Players','title'=>'Competitors','desc'=>'Create an account, join a team, and enter tournaments with your roster. Track your historical stats on the global leaderboard and climb the competitive ranks.','link'=>'/guide.php#tab-players','lbl'=>'Player guide →'],
                ['icon'=>'👑','badge'=>'Captains','title'=>'Team Captains','desc'=>'Create your team, manage your roster, register for tournaments, and coordinate your players\'s check-ins before every competition.','link'=>'/guide.php#tab-captains','lbl'=>'Captain guide →'],
                ['icon'=>'⚙️','badge'=>'Admins','title'=>'Organisers','desc'=>'Create events and tournaments, assign seeds, generate automatic brackets, report scores, and receive real-time Discord notifications. Zero spreadsheets.','link'=>'/guide.php#tab-admins','lbl'=>'Organiser guide →'],
            ];
            foreach ($audiences as $a):
            ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;display:flex;flex-direction:column;gap:0;transition:border-color 0.2s var(--ease-out);" onmouseover="this.style.borderColor='rgba(0,212,255,0.35)'" onmouseout="this.style.borderColor='var(--border)'">
                <div style="font-size:32px;margin-bottom:12px;"><?= $a['icon'] ?></div>
                <span class="badge badge-blue" style="width:fit-content;margin-bottom:10px;"><?= htmlspecialchars($a['badge'], ENT_QUOTES) ?></span>
                <div style="font-family:var(--font-display);font-size:18px;font-weight:800;margin-bottom:10px;"><?= htmlspecialchars($a['title'], ENT_QUOTES) ?></div>
                <div style="color:var(--text-secondary);font-size:14px;line-height:1.65;flex:1;"><?= htmlspecialchars($a['desc'], ENT_QUOTES) ?></div>
                <a href="<?= htmlspecialchars($a['link'], ENT_QUOTES) ?>" onclick="<?= strpos($a['link'], '#tab-') !== false ? 'event.preventDefault();window.location=\'' . explode('#', $a['link'])[0] . '\';localStorage.setItem(\'guideTab\',\'' . explode('tab-', $a['link'])[1] . '\')' : '' ?>" style="display:inline-flex;align-items:center;gap:4px;color:var(--accent-blue);font-size:13px;font-weight:600;text-decoration:none;margin-top:16px;">
                    <?= htmlspecialchars($a['lbl'], ENT_QUOTES) ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- How it works timeline -->
    <div class="reveal" style="margin-bottom:72px;">
        <span class="section-label"><?= $isIt ? 'Come Funziona' : 'How It Works' ?></span>
        <h2 style="font-size:clamp(22px,3vw,34px);font-weight:800;letter-spacing:-0.8px;margin-bottom:40px;">
            <?= $isIt ? 'Il ciclo di vita di un torneo' : 'The lifecycle of a tournament' ?>
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0;background:var(--border);border-radius:var(--radius-lg);overflow:hidden;">
            <?php
            $lifecycle = $isIt ? [
                ['num'=>'01','title'=>'Registrazione','desc'=>'I team si iscrivono autonomamente tramite la pagina evento.'],
                ['num'=>'02','title'=>'Seeding','desc'=>'L\'admin assegna i seed per un bracket equilibrato.'],
                ['num'=>'03','title'=>'Check-in','desc'=>'I team confermano la loro presenza prima dell\'inizio.'],
                ['num'=>'04','title'=>'Bracket','desc'=>'Il sistema genera automaticamente tutti i round.'],
                ['num'=>'05','title'=>'Competizione','desc'=>'I match si giocano, i punteggi vengono registrati live.'],
                ['num'=>'06','title'=>'Premiazione','desc'=>'Il vincitore viene incoronato e i piazzamenti salvati.'],
            ] : [
                ['num'=>'01','title'=>'Registration','desc'=>'Teams register themselves via the event page.'],
                ['num'=>'02','title'=>'Seeding','desc'=>'Admin assigns seeds for a balanced bracket.'],
                ['num'=>'03','title'=>'Check-in','desc'=>'Teams confirm their attendance before start.'],
                ['num'=>'04','title'=>'Bracket','desc'=>'The system auto-generates all match rounds.'],
                ['num'=>'05','title'=>'Competition','desc'=>'Matches are played, scores are recorded live.'],
                ['num'=>'06','title'=>'Champion','desc'=>'The winner is crowned and placements saved.'],
            ];
            foreach ($lifecycle as $li => $lc):
            ?>
            <div style="background:var(--bg-secondary);padding:24px 20px;position:relative;">
                <div style="font-family:var(--font-display);font-size:28px;font-weight:800;color:rgba(0,212,255,0.2);letter-spacing:-1px;margin-bottom:8px;"><?= $lc['num'] ?></div>
                <div style="font-family:var(--font-display);font-size:14px;font-weight:700;margin-bottom:6px;"><?= htmlspecialchars($lc['title'], ENT_QUOTES) ?></div>
                <div style="font-size:13px;color:var(--text-secondary);line-height:1.5;"><?= htmlspecialchars($lc['desc'], ENT_QUOTES) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tech stack -->
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:36px;margin-bottom:72px;">
        <span class="section-label"><?= $isIt ? 'Tecnologia' : 'Technology' ?></span>
        <h2 style="font-size:20px;font-weight:800;margin-bottom:12px;">
            <?= $isIt ? 'Costruita per la performance' : 'Built for performance' ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:14px;line-height:1.7;max-width:640px;margin-bottom:24px;">
            <?= $isIt
                ? 'Tech Dragons Events è costruita su uno stack PHP/MySQL senza dipendenze pesanti, garantendo velocità, sicurezza e semplicità di deployment. Ogni pagina è ottimizzata per dispositivi mobile e include meta SEO, hreflang e structured data JSON-LD.'
                : 'Tech Dragons Events is built on a lightweight PHP/MySQL stack with no heavy dependencies, ensuring speed, security, and simple deployment. Every page is mobile-optimised and includes SEO meta, hreflang, and JSON-LD structured data.' ?>
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php
            $tech = ['PHP 8.2','MySQL / MariaDB','PDO + Argon2ID','GSAP 3','Three.js','Discord Webhooks','Single-Elimination Brackets','Bilingual EN/IT'];
            foreach ($tech as $t):
            ?>
            <span style="background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 14px;font-size:12px;font-weight:600;color:var(--text-secondary);">
                <?= htmlspecialchars($t, ENT_QUOTES) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- CTA -->
    <div class="reveal" style="text-align:center;padding:56px 32px;background:linear-gradient(135deg,rgba(0,212,255,0.06),rgba(102,126,234,0.06));border:1px solid rgba(0,212,255,0.15);border-radius:var(--radius-lg);margin-bottom:16px;">
        <h2 style="font-size:clamp(24px,4vw,40px);font-weight:800;letter-spacing:-1px;margin-bottom:14px;">
            <?= $isIt ? 'Pronto a iniziare?' : 'Ready to get started?' ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:15px;max-width:460px;margin:0 auto 28px;">
            <?= $isIt
                ? 'Crea il tuo account gratuitamente, entra in un team e competiti nella prossima edizione.'
                : 'Create your free account, join a team, and compete in the next edition.' ?>
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="/register.php" class="btn-primary" style="padding:13px 32px;font-size:15px;">
                <?= $isIt ? '🚀 Registrati Gratis' : '🚀 Register Free' ?>
            </a>
            <a href="/guide.php" class="btn-secondary" style="padding:13px 32px;font-size:15px;">
                <?= $isIt ? 'Leggi la Guida' : 'Read the Guide' ?>
            </a>
        </div>
    </div>

</div>
</main>

<script>
// If arriving from about.php audience card, switch guide tab
if (window.location.pathname === '/guide.php') {
    var savedTab = localStorage.getItem('guideTab');
    if (savedTab) { localStorage.removeItem('guideTab'); }
}
</script>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
