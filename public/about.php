<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$isIt = (($_COOKIE['lang'] ?? 'it') === 'it');

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
    <div class="reveal" style="margin-bottom:72px;padding-top:8px;max-width:760px;">
        <span class="section-label"><?= $isIt ? 'Chi Siamo' : 'About' ?></span>
        <h1 style="font-size:clamp(36px,6vw,64px);font-weight:800;letter-spacing:-2px;margin-bottom:24px;line-height:1.05;">
            <?= $isIt
                ? 'Tornei esports. Senza il casino.'
                : 'Esports tournaments. Without the chaos.' ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:17px;line-height:1.75;max-width:580px;margin-bottom:16px;">
            <?= $isIt
                ? 'Tech Dragons Events è il posto dove i team di gaming si sfidano. Ti iscrivi, giochi e i risultati restano lì — bracket, classifica, storico delle partite.'
                : 'Tech Dragons Events is where gaming teams compete. You sign up, you play, and the results stick around — brackets, leaderboards, match history.' ?>
        </p>
        <p style="color:var(--text-secondary);font-size:17px;line-height:1.75;max-width:580px;">
            <?= $isIt
                ? 'Niente spreadsheet condivisi. Niente chi-gioca-contro-chi scritto su un foglio. Solo il torneo.'
                : 'No shared spreadsheets. No who-plays-who written on a piece of paper. Just the tournament.' ?>
        </p>
    </div>

    <!-- Live stats -->
    <div class="reveal" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1px;background:var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:72px;">
        <?php
        $statItems = [
            ['value' => number_format((int)$stats['events']),      'label' => $isIt ? 'Eventi'    : 'Events'],
            ['value' => number_format((int)$stats['tournaments']), 'label' => $isIt ? 'Tornei'    : 'Tournaments'],
            ['value' => number_format((int)$stats['teams']),       'label' => $isIt ? 'Team'      : 'Teams'],
            ['value' => number_format((int)$stats['players']),     'label' => $isIt ? 'Giocatori' : 'Players'],
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

    <!-- Story -->
    <div class="reveal" style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;margin-bottom:80px;">
        <div>
            <span class="section-label"><?= $isIt ? 'Perché Esiste' : 'Why It Exists' ?></span>
            <h2 style="font-size:clamp(22px,3.5vw,36px);font-weight:800;letter-spacing:-1px;margin-bottom:20px;line-height:1.2;">
                <?= $isIt
                    ? 'Organizzare un torneo non dovrebbe richiedere due giorni di lavoro'
                    : 'Organising a tournament shouldn\'t take two days of work' ?>
            </h2>
            <p style="color:var(--text-secondary);font-size:15px;line-height:1.75;margin-bottom:16px;">
                <?= $isIt
                    ? 'Ci siamo stancati dello stesso copione: un Google Sheet che si corrompe a metà evento, venti messaggi Discord su chi deve giocare contro chi, e qualcuno che chiede "ok ma chi ha vinto?" dopo ogni partita.'
                    : 'We got tired of the same routine every time: a Google Sheet that breaks mid-event, twenty Discord messages about who plays who, and someone asking "wait who won that?" after every match.' ?>
            </p>
            <p style="color:var(--text-secondary);font-size:15px;line-height:1.75;">
                <?= $isIt
                    ? 'Tech Dragons risolve questo. I team si iscrivono da soli. Assegni i seed. Un click genera il bracket completo. I punteggi aggiornano il tabellone in tempo reale. Alla fine hai una classifica con tutto lo storico.'
                    : 'Tech Dragons fixes that. Teams sign up themselves. You set seeds. One click builds the full bracket. Scores update the draw in real time. You end up with a leaderboard that tracks everything.' ?>
            </p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <?php
            $pillars = $isIt ? [
                ['icon'=>'🏆','title'=>'Bracket veri','desc'=>'Eliminazione singola con seeding, bye automatici e avanzamento dei vincitori. Funziona e basta.'],
                ['icon'=>'🛡️','title'=>'Ruoli separati','desc'=>'Admin, capitani e giocatori vedono e fanno cose diverse. Nessuno tocca quello che non deve.'],
                ['icon'=>'📊','title'=>'Statistiche permanenti','desc'=>'Ogni match aggiorna la classifica globale. Vittorie, sconfitte, win rate, premi — tutto lì per sempre.'],
                ['icon'=>'🔔','title'=>'Discord','desc'=>'Incolla un webhook e il tuo server riceve un messaggio per ogni risultato e per il vincitore finale.'],
            ] : [
                ['icon'=>'🏆','title'=>'Real brackets','desc'=>'Single elimination with proper seeding, automatic byes, and winners advancing. It just works.'],
                ['icon'=>'🛡️','title'=>'Separate roles','desc'=>'Admins, captains, and players each see and do different things. Nobody touches what they shouldn\'t.'],
                ['icon'=>'📊','title'=>'Permanent stats','desc'=>'Every match feeds the leaderboard. Wins, losses, win rate, prize money — tracked forever.'],
                ['icon'=>'🔔','title'=>'Discord','desc'=>'Paste a webhook and your server gets a message for every result and when the champion is crowned.'],
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
        <span class="section-label"><?= $isIt ? 'Per Chi' : 'Who It\'s For' ?></span>
        <h2 style="font-size:clamp(22px,3vw,34px);font-weight:800;letter-spacing:-0.8px;margin-bottom:32px;">
            <?= $isIt ? 'Tre tipi di utenti. Una sola piattaforma.' : 'Three types of users. One platform.' ?>
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;">
            <?php
            $audiences = $isIt ? [
                ['icon'=>'🎮','badge'=>'Giocatori','title'=>'Vuoi solo giocare','desc'=>'Fai un account, entra in un team, partecipa. I tuoi risultati si accumulano nella classifica nel tempo.','link'=>'/guide.php','lbl'=>'Guida per giocatori →'],
                ['icon'=>'👑','badge'=>'Capitani','title'=>'Gestisci la tua squadra','desc'=>'Crea il team, aggiungi i giocatori, iscriviti ai tornei. Sei tu a portare il team in gara.','link'=>'/guide.php','lbl'=>'Guida per capitani →'],
                ['icon'=>'⚙️','badge'=>'Organizzatori','title'=>'Gestisci i tornei','desc'=>'Crea eventi, genera bracket, inserisci i punteggi. Niente spreadsheet — tutto sulla piattaforma.','link'=>'/guide.php','lbl'=>'Guida per organizzatori →'],
            ] : [
                ['icon'=>'🎮','badge'=>'Players','title'=>'You just want to play','desc'=>'Make an account, get on a team, enter. Your results build up on the leaderboard over time.','link'=>'/guide.php','lbl'=>'Player guide →'],
                ['icon'=>'👑','badge'=>'Captains','title'=>'You run a squad','desc'=>'Create the team, add players, enter tournaments. You\'re the one who takes the team to competition.','link'=>'/guide.php','lbl'=>'Captain guide →'],
                ['icon'=>'⚙️','badge'=>'Admins','title'=>'You run tournaments','desc'=>'Create events, generate brackets, enter scores. No spreadsheets — everything on the platform.','link'=>'/guide.php','lbl'=>'Organiser guide →'],
            ];
            foreach ($audiences as $a):
            ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;display:flex;flex-direction:column;transition:border-color 0.2s var(--ease-out);" onmouseover="this.style.borderColor='rgba(0,212,255,0.35)'" onmouseout="this.style.borderColor='var(--border)'">
                <div style="font-size:32px;margin-bottom:12px;"><?= $a['icon'] ?></div>
                <span class="badge badge-blue" style="width:fit-content;margin-bottom:10px;"><?= htmlspecialchars($a['badge'], ENT_QUOTES) ?></span>
                <div style="font-family:var(--font-display);font-size:18px;font-weight:800;margin-bottom:10px;"><?= htmlspecialchars($a['title'], ENT_QUOTES) ?></div>
                <div style="color:var(--text-secondary);font-size:14px;line-height:1.65;flex:1;"><?= htmlspecialchars($a['desc'], ENT_QUOTES) ?></div>
                <a href="<?= htmlspecialchars($a['link'], ENT_QUOTES) ?>" style="display:inline-flex;align-items:center;gap:4px;color:var(--accent-blue);font-size:13px;font-weight:600;text-decoration:none;margin-top:16px;">
                    <?= htmlspecialchars($a['lbl'], ENT_QUOTES) ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- How a tournament goes -->
    <div class="reveal" style="margin-bottom:72px;">
        <span class="section-label"><?= $isIt ? 'Come Va un Torneo' : 'How a Tournament Goes' ?></span>
        <h2 style="font-size:clamp(22px,3vw,34px);font-weight:800;letter-spacing:-0.8px;margin-bottom:40px;">
            <?= $isIt ? 'Dall\'iscrizione al vincitore' : 'From sign-up to champion' ?>
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1px;background:var(--border);border-radius:var(--radius-lg);overflow:hidden;">
            <?php
            $lifecycle = $isIt ? [
                ['num'=>'01','title'=>'Iscrizione',  'desc'=>'I team si registrano dalla pagina evento.'],
                ['num'=>'02','title'=>'Seed',         'desc'=>'L\'admin numera i team per forza.'],
                ['num'=>'03','title'=>'Check-in',     'desc'=>'Le squadre confermano la presenza.'],
                ['num'=>'04','title'=>'Bracket',      'desc'=>'Il sistema genera il tabellone.'],
                ['num'=>'05','title'=>'Partite',      'desc'=>'Si gioca, i punteggi vengono inseriti.'],
                ['num'=>'06','title'=>'Vincitore',    'desc'=>'Il campione viene incoronato. Tutto salvato.'],
            ] : [
                ['num'=>'01','title'=>'Sign-up',    'desc'=>'Teams register from the event page.'],
                ['num'=>'02','title'=>'Seeds',      'desc'=>'Admin ranks the teams by strength.'],
                ['num'=>'03','title'=>'Check-in',   'desc'=>'Squads confirm they\'re showing up.'],
                ['num'=>'04','title'=>'Bracket',    'desc'=>'System builds the full draw.'],
                ['num'=>'05','title'=>'Matches',    'desc'=>'Games happen, scores go in.'],
                ['num'=>'06','title'=>'Champion',   'desc'=>'Winner crowned. Everything saved.'],
            ];
            foreach ($lifecycle as $lc):
            ?>
            <div style="background:var(--bg-secondary);padding:24px 20px;">
                <div style="font-family:var(--font-display);font-size:28px;font-weight:800;color:rgba(0,212,255,0.2);letter-spacing:-1px;margin-bottom:8px;"><?= $lc['num'] ?></div>
                <div style="font-family:var(--font-display);font-size:14px;font-weight:700;margin-bottom:6px;"><?= htmlspecialchars($lc['title'], ENT_QUOTES) ?></div>
                <div style="font-size:13px;color:var(--text-secondary);line-height:1.5;"><?= htmlspecialchars($lc['desc'], ENT_QUOTES) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Stack -->
    <div class="reveal" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:36px;margin-bottom:72px;">
        <span class="section-label"><?= $isIt ? 'Come È Fatto' : 'How It\'s Built' ?></span>
        <h2 style="font-size:20px;font-weight:800;margin-bottom:12px;">
            <?= $isIt ? 'Veloce, senza fronzoli' : 'Fast, no frills' ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:14px;line-height:1.7;max-width:600px;margin-bottom:24px;">
            <?= $isIt
                ? 'PHP e MySQL, senza dipendenze pesanti. Carica veloce, funziona su mobile, bilingue in italiano e inglese. Nessun framework gonfiato — solo quello che serve.'
                : 'PHP and MySQL, no heavy dependencies. Loads fast, works on mobile, bilingual in Italian and English. No bloated framework — just what\'s needed.' ?>
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php
            $tech = ['PHP 8.2','MySQL / MariaDB','PDO + Argon2ID','GSAP 3','Three.js','Discord Webhooks','Single Elimination','EN / IT'];
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
            <?= $isIt ? 'Entra. È gratis.' : 'Join. It\'s free.' ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:15px;max-width:380px;margin:0 auto 28px;">
            <?= $isIt
                ? 'Crea un account, trova il tuo team e gioca nella prossima edizione.'
                : 'Create an account, find your team, and play in the next edition.' ?>
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="/register.php" class="btn-primary" style="padding:13px 32px;font-size:15px;">
                <?= $isIt ? '🚀 Registrati' : '🚀 Register' ?>
            </a>
            <a href="/guide.php" class="btn-secondary" style="padding:13px 32px;font-size:15px;">
                <?= $isIt ? 'Leggi la Guida' : 'Read the Guide' ?>
            </a>
        </div>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
