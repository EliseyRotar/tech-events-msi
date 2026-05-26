<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$isIt = (($_COOKIE['lang'] ?? 'it') === 'it');

$pageTitle = $isIt
    ? 'Guida alla Piattaforma — Tech Dragons Events'
    : 'Platform Guide — Tech Dragons Events';

require_once __DIR__ . '/../templates/layout/header.php';
?>
<link rel="stylesheet" href="/assets/css/bracket.css">
<style>
.guide-tab {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    color: var(--text-secondary);
    cursor: pointer;
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 700;
    padding: 10px 24px;
    transition: all 0.2s var(--ease-out);
    letter-spacing: 0.03em;
}
.guide-tab:hover { border-color: var(--accent-blue); color: var(--accent-blue); }
.guide-tab.active { background: var(--accent-blue); border-color: var(--accent-blue); color: #000; }
.step-card {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px 28px;
    margin-bottom: 14px;
    transition: border-color 0.2s var(--ease-out);
}
.step-card:hover { border-color: rgba(0,212,255,0.3); }
.step-num {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg,rgba(0,212,255,0.12),rgba(102,126,234,0.12));
    border: 1px solid rgba(0,212,255,0.3);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display); font-size: 16px; font-weight: 800;
    color: var(--accent-blue); flex-shrink: 0;
}
.step-icon { font-size: 22px; margin-bottom: 4px; }
.step-title { font-family: var(--font-display); font-size: 16px; font-weight: 700; margin-bottom: 6px; }
.step-desc { color: var(--text-secondary); font-size: 14px; line-height: 1.65; }
.step-link {
    display: inline-flex; align-items: center; gap: 4px;
    color: var(--accent-blue); font-size: 13px; font-weight: 600;
    text-decoration: none; margin-top: 10px;
}
.step-link:hover { opacity: 0.75; }
.faq-item { border-bottom: 1px solid var(--border); }
.faq-btn {
    background: none; border: none; color: var(--text-primary);
    cursor: pointer; font-family: var(--font-body); font-size: 15px;
    font-weight: 600; padding: 18px 0; text-align: left; width: 100%;
    display: flex; justify-content: space-between; align-items: center; gap: 16px;
}
.faq-chevron { font-size: 11px; color: var(--text-secondary); transition: transform 0.2s; flex-shrink: 0; }
.faq-btn[aria-expanded="true"] .faq-chevron { transform: rotate(180deg); }
.faq-body {
    color: var(--text-secondary); font-size: 14px;
    line-height: 1.7; padding-bottom: 18px; display: none;
}
.faq-body.open { display: block; }
</style>

<main class="page-main">
<div class="container">

    <!-- Hero -->
    <div class="reveal" style="text-align:center;margin-bottom:64px;padding-top:8px;">
        <span class="section-label"><?= $isIt ? 'Documentazione' : 'Documentation' ?></span>
        <h1 style="font-size:clamp(32px,5vw,56px);font-weight:800;letter-spacing:-1.5px;margin-bottom:16px;">
            <?= $isIt ? 'Guida alla Piattaforma' : 'Platform Guide' ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:16px;max-width:560px;margin:0 auto 28px;">
            <?= $isIt
                ? 'Tutto quello che devi sapere per partecipare, gestire il tuo team o organizzare tornei su Tech Dragons Events.'
                : 'Everything you need to know to participate, manage your team, or run tournaments on Tech Dragons Events.' ?>
        </p>
    </div>

    <!-- Tab switcher -->
    <div class="reveal" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:48px;">
        <button class="guide-tab active" data-tab="players" onclick="switchTab('players')">
            🎮 <?= $isIt ? 'Giocatori' : 'Players' ?>
        </button>
        <button class="guide-tab" data-tab="captains" onclick="switchTab('captains')">
            👑 <?= $isIt ? 'Capitani di Team' : 'Team Captains' ?>
        </button>
        <button class="guide-tab" data-tab="admins" onclick="switchTab('admins')">
            ⚙️ <?= $isIt ? 'Organizzatori' : 'Organisers' ?>
        </button>
    </div>

    <?php
    /* ── DATA ─────────────────────────────────────────────────────────── */
    $sections = [
        'players' => [
            'label' => $isIt ? 'Per i Giocatori' : 'For Players',
            'title' => $isIt ? 'Come Partecipare a un Torneo' : 'How to Enter a Tournament',
            'sub'   => $isIt
                ? 'Segui questi passi dalla registrazione fino alla gara.'
                : 'Follow these steps from registration all the way to competition.',
            'steps' => $isIt ? [
                ['icon'=>'📝','title'=>'Crea il tuo account','desc'=>'Registrati su Tech Dragons Events con il tuo nome, email e gamertag. La registrazione è completamente gratuita e richiede meno di due minuti.','link'=>'/register.php','lbl'=>'Registrati →'],
                ['icon'=>'🔍','title'=>'Trova un evento','desc'=>'Sfoglia la pagina degli eventi attivi per trovare competizioni aperte. Ogni card mostra il gioco, il formato del torneo, il montepremi e i posti disponibili.','link'=>'/#events','lbl'=>'Vedi gli eventi →'],
                ['icon'=>'🛡️','title'=>'Entra in un team','desc'=>'Ricevi un invito da un capitano oppure crea il tuo team dalla Dashboard. È obbligatorio far parte di un team per iscriversi a qualsiasi torneo.','link'=>'/addTeam.php','lbl'=>'Crea un team →'],
                ['icon'=>'📋','title'=>'Iscriviti al torneo','desc'=>'Una volta che il tuo team è pronto, il capitano può iscriverlo a qualsiasi torneo aperto direttamente dalla pagina dell\'evento, usando il pulsante "Iscriviti".','link'=>null,'lbl'=>null],
                ['icon'=>'✅','title'=>'Fai il check-in','desc'=>'Prima dell\'inizio del torneo si apre una finestra di check-in. Vai alla pagina di check-in e conferma la tua presenza. I team che saltano il check-in possono essere rimossi dal bracket e sostituiti.','link'=>null,'lbl'=>null],
                ['icon'=>'🏆','title'=>'Trova il tuo match','desc'=>'Dopo la generazione del bracket vai alla pagina del torneo per vedere il tuo avversario, l\'orario della partita e il tabellone completo. Clicca su un match per i dettagli in tempo reale.','link'=>null,'lbl'=>null],
                ['icon'=>'📊','title'=>'Segui i risultati','desc'=>'I punteggi vengono aggiornati dagli organizzatori in tempo reale. Il bracket avanza i vincitori automaticamente. Controlla la Classifica per le statistiche storiche del tuo team.','link'=>'/leaderboard.php','lbl'=>'Vedi la Classifica →'],
            ] : [
                ['icon'=>'📝','title'=>'Create your account','desc'=>'Register on Tech Dragons Events with your name, email, and gamertag. Registration is completely free and takes under two minutes.','link'=>'/register.php','lbl'=>'Register →'],
                ['icon'=>'🔍','title'=>'Find an event','desc'=>'Browse the active events page for open competitions. Each card shows the game, tournament format, prize pool, and available team slots.','link'=>'/#events','lbl'=>'View events →'],
                ['icon'=>'🛡️','title'=>'Join a team','desc'=>'Get invited by a team captain, or create your own team from the Dashboard. You must be part of a team to enter any tournament.','link'=>'/addTeam.php','lbl'=>'Create a team →'],
                ['icon'=>'📋','title'=>'Enter the tournament','desc'=>'Once your team is set up, your captain can register it for any open tournament directly from the event page using the "Enter" button.','link'=>null,'lbl'=>null],
                ['icon'=>'✅','title'=>'Check in','desc'=>'Before the tournament starts, a check-in window opens. Go to the check-in page and confirm your team\'s attendance. Teams that miss check-in may be removed from the bracket and replaced.','link'=>null,'lbl'=>null],
                ['icon'=>'🏆','title'=>'Find your match','desc'=>'Once the bracket is generated, go to the tournament bracket page to see your opponent, your scheduled match time, and the full draw. Click any match for real-time details.','link'=>null,'lbl'=>null],
                ['icon'=>'📊','title'=>'Track results','desc'=>'Scores are updated live by organisers. The bracket auto-advances winners. Check the Leaderboard for your team\'s long-term historical stats.','link'=>'/leaderboard.php','lbl'=>'View Leaderboard →'],
            ],
        ],
        'captains' => [
            'label' => $isIt ? 'Per i Capitani' : 'For Team Captains',
            'title' => $isIt ? 'Come Gestire un Team' : 'How to Manage a Team',
            'sub'   => $isIt
                ? 'Crea il tuo roster, iscriviti ai tornei e coordina i check-in.'
                : 'Build your roster, enter tournaments, and coordinate check-ins.',
            'steps' => $isIt ? [
                ['icon'=>'👑','title'=>'Crea il tuo team','desc'=>'Dalla Dashboard vai su "Registra Team". Inserisci il nome del team, la descrizione e facoltativamente il link allo sponsor. Diventi automaticamente capitano.','link'=>'/addTeam.php','lbl'=>'Registra team →'],
                ['icon'=>'👥','title'=>'Costruisci il roster','desc'=>'Invita i giocatori tramite username o email. Dalla pagina del team puoi gestire i membri, rimuovere giocatori inattivi e aggiornare i ruoli nel gioco.','link'=>null,'lbl'=>null],
                ['icon'=>'📋','title'=>'Iscriviti a un torneo','desc'=>'Trova un torneo aperto sulla pagina dell\'evento e clicca "Iscriviti". Il tuo team viene aggiunto alla lista dei partecipanti immediatamente, a patto che ci siano posti liberi.','link'=>'/#events','lbl'=>'Sfoglia tornei →'],
                ['icon'=>'✅','title'=>'Gestisci il check-in','desc'=>'Quando l\'organizzatore apre la finestra di check-in, vai alla pagina check-in del torneo. Puoi confermare la presenza del tuo team in un solo click. Monitora anche gli altri team.','link'=>null,'lbl'=>null],
                ['icon'=>'📊','title'=>'Segui il bracket','desc'=>'Una volta generato il bracket puoi seguire ogni partita in tempo reale. I risultati aggiornano automaticamente i seed e i posizionamenti finali nella classifica globale.','link'=>'/leaderboard.php','lbl'=>'Classifica globale →'],
            ] : [
                ['icon'=>'👑','title'=>'Create your team','desc'=>'From the Dashboard go to "Register Team". Set your team name, description, and optionally a sponsor link. You automatically become the captain.','link'=>'/addTeam.php','lbl'=>'Register team →'],
                ['icon'=>'👥','title'=>'Build your roster','desc'=>'Invite players by username or email. From your team page you can manage members, remove inactive players, and update in-game roles.','link'=>null,'lbl'=>null],
                ['icon'=>'📋','title'=>'Enter a tournament','desc'=>'Find an open tournament on the event page and click "Enter". Your team is added to the participant list immediately, provided slots are available.','link'=>'/#events','lbl'=>'Browse tournaments →'],
                ['icon'=>'✅','title'=>'Manage check-in','desc'=>'When the organiser opens the check-in window, go to the tournament check-in page. You can confirm your team\'s attendance with a single click and monitor other teams.','link'=>null,'lbl'=>null],
                ['icon'=>'📊','title'=>'Follow the bracket','desc'=>'Once the bracket is generated you can follow every match in real time. Results automatically update seeds and final placements in the global leaderboard.','link'=>'/leaderboard.php','lbl'=>'Global leaderboard →'],
            ],
        ],
        'admins' => [
            'label' => $isIt ? 'Per gli Organizzatori' : 'For Organisers',
            'title' => $isIt ? 'Come Organizzare un Torneo' : 'How to Run a Tournament',
            'sub'   => $isIt
                ? 'Dalla creazione dell\'evento alla cerimonia di premiazione, tutto in un\'unica piattaforma.'
                : 'From creating the event to crowning a champion — all in one platform.',
            'steps' => $isIt ? [
                ['icon'=>'🌐','title'=>'Crea un evento','desc'=>'Dalla Dashboard admin, crea un nuovo evento con nome, data, luogo (LAN o online) e descrizione. L\'evento è il contenitore che raggruppa uno o più tornei.','link'=>'/dashboard.php','lbl'=>'Apri Dashboard →'],
                ['icon'=>'🏆','title'=>'Aggiungi un torneo','desc'=>'All\'interno dell\'evento crea un torneo: scegli il gioco, il formato (single elimination), il montepremi e il numero massimo di team ammessi.','link'=>null,'lbl'=>null],
                ['icon'=>'👥','title'=>'Monitora le iscrizioni','desc'=>'I team si iscrivono autonomamente dalla pagina dell\'evento. Dalla pagina "Gestisci Match" puoi vedere tutti i team iscritti e assegnare i seed manualmente.','link'=>null,'lbl'=>null],
                ['icon'=>'🌱','title'=>'Assegna i seed','desc'=>'Nella tabella "Team Seeding" assegna un numero di seed a ogni team (1 = testa di serie). Il bracket posizionerà i seed 1 e 2 nel ramo opposto: si incontreranno solo in finale.','link'=>null,'lbl'=>null],
                ['icon'=>'⏰','title'=>'Apri il check-in','desc'=>'Clicca "Apri Finestra Check-in" per abilitare i team a confermare la loro presenza. Il torneo passa in stato "checkin" e la pagina check-in diventa attiva.','link'=>null,'lbl'=>null],
                ['icon'=>'⚡','title'=>'Genera il bracket','desc'=>'Clicca "Genera Bracket" — il sistema posiziona i team per seed, crea tutti i round, collega automaticamente ogni match al successivo e gestisce i bye per le parentesi non piene.','link'=>null,'lbl'=>null],
                ['icon'=>'📋','title'=>'Registra i punteggi','desc'=>'Dopo ogni match clicca "Score" sulla riga del match. Inserisci i punteggi e seleziona "Segna Completato" — il vincitore avanza automaticamente al prossimo round. L\'ultimo match chiude il torneo e registra i piazzamenti.','link'=>null,'lbl'=>null],
                ['icon'=>'🔔','title'=>'Notifiche Discord','desc'=>'Aggiungi un webhook Discord nella sezione "Notifiche" della pagina Gestisci Match. Riceverai notifiche automatiche per l\'inizio del torneo, ogni risultato di match e il vincitore finale.','link'=>null,'lbl'=>null],
            ] : [
                ['icon'=>'🌐','title'=>'Create an event','desc'=>'From the admin Dashboard, create a new event with name, date, location (LAN or online), and description. The event is the container that groups one or more tournaments.','link'=>'/dashboard.php','lbl'=>'Open Dashboard →'],
                ['icon'=>'🏆','title'=>'Add a tournament','desc'=>'Inside the event, create a tournament: choose the game, format (single elimination), prize pool, and maximum number of teams allowed.','link'=>null,'lbl'=>null],
                ['icon'=>'👥','title'=>'Monitor registrations','desc'=>'Teams register themselves from the event page. From the "Manage Match" page you can see all registered teams and manually assign seeds.','link'=>null,'lbl'=>null],
                ['icon'=>'🌱','title'=>'Assign seeds','desc'=>'In the "Team Seeding" table assign a seed number to each team (1 = top seed). The bracket places seeds 1 and 2 in opposite halves — they can only meet in the final.','link'=>null,'lbl'=>null],
                ['icon'=>'⏰','title'=>'Open check-in','desc'=>'Click "Open Check-in Window" to let teams confirm their attendance. The tournament moves to "checkin" status and the check-in page becomes active for participants.','link'=>null,'lbl'=>null],
                ['icon'=>'⚡','title'=>'Generate the bracket','desc'=>'Click "Generate Bracket" — the system seeds teams, creates all match rounds, links each match to the next automatically, and handles byes for non-power-of-two brackets.','link'=>null,'lbl'=>null],
                ['icon'=>'📋','title'=>'Report scores','desc'=>'After each match click "Score" on the match row. Enter the scores and select "Mark Completed" — the winner advances automatically to the next round. The final match closes the tournament and records placements.','link'=>null,'lbl'=>null],
                ['icon'=>'🔔','title'=>'Discord notifications','desc'=>'Add a Discord webhook in the "Notifications" section of the Manage Match page. You will receive automatic notifications for tournament start, every match result, and the final champion.','link'=>null,'lbl'=>null],
            ],
        ],
    ];
    /* ── RENDER SECTIONS ──────────────────────────────────────────────── */
    foreach ($sections as $tabId => $section):
    ?>
    <div id="tab-<?= $tabId ?>" class="guide-section" <?= $tabId !== 'players' ? 'style="display:none;"' : '' ?>>
        <div style="margin-bottom:32px;" class="reveal">
            <span class="section-label"><?= $section['label'] ?></span>
            <h2 style="font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:8px;">
                <?= $section['title'] ?>
            </h2>
            <p style="color:var(--text-secondary);font-size:14px;"><?= $section['sub'] ?></p>
        </div>
        <?php foreach ($section['steps'] as $i => $step): ?>
        <div class="step-card reveal">
            <div class="step-num"><?= $i + 1 ?></div>
            <div style="flex:1;">
                <div class="step-icon"><?= $step['icon'] ?></div>
                <div class="step-title"><?= htmlspecialchars($step['title'], ENT_QUOTES) ?></div>
                <div class="step-desc"><?= htmlspecialchars($step['desc'], ENT_QUOTES) ?></div>
                <?php if ($step['link']): ?>
                <a href="<?= htmlspecialchars($step['link'], ENT_QUOTES) ?>" class="step-link">
                    <?= htmlspecialchars($step['lbl'], ENT_QUOTES) ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <!-- FAQ -->
    <div class="reveal" style="margin-top:72px;padding-top:48px;border-top:1px solid var(--border);">
        <span class="section-label">FAQ</span>
        <h2 style="font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:32px;">
            <?= $isIt ? 'Domande Frequenti' : 'Frequently Asked Questions' ?>
        </h2>
        <div style="max-width:760px;">
        <?php
        $faqs = $isIt ? [
            ['q'=>'È gratuito partecipare?', 'a'=>'Sì, la registrazione alla piattaforma è completamente gratuita. I montepremi vengono definiti dagli organizzatori degli eventi e la loro distribuzione è di loro esclusiva responsabilità.'],
            ['q'=>'Ho bisogno di un team per competere?', 'a'=>'Sì, tutti i tornei su Tech Dragons Events sono a squadre. Puoi creare un team anche da solo (per format in singolo) oppure farti invitare da un capitano.'],
            ['q'=>'Come viene calcolato il bracket?', 'a'=>'Usiamo il sistema di seeding standard per i bracket a eliminazione singola. Il seed 1 e il seed 2 vengono posizionati nei rami opposti e possono incontrarsi solo in finale. I bye vengono assegnati automaticamente se il numero di team non è una potenza di 2.'],
            ['q'=>'Cosa succede se un team non fa il check-in?', 'a'=>'Un team che non conferma la propria presenza durante la finestra di check-in può essere rimosso dal bracket dagli organizzatori. Questo garantisce che il torneo si svolga senza slot vuoti.'],
            ['q'=>'Chi registra i punteggi dei match?', 'a'=>'I punteggi vengono inseriti dagli amministratori/arbitri della piattaforma. Il bracket avanza automaticamente non appena un risultato viene confermato. Non è prevista al momento la segnalazione autonoma da parte dei giocatori.'],
            ['q'=>'Come segnalo una contestazione?', 'a'=>'Contatta direttamente l\'organizzatore dell\'evento tramite la sezione Contatto o via Discord. Tech Dragons Events fornisce l\'infrastruttura tecnica ma non arbitrisce le controversie tra team.'],
            ['q'=>'Le mie statistiche appaiono subito in classifica?', 'a'=>'Sì, la Classifica si aggiorna automaticamente dopo ogni match completato. Puoi visualizzare vittorie, sconfitte, win rate e montepremi vinti per ogni team nella pagina dedicata.'],
        ] : [
            ['q'=>'Is it free to participate?', 'a'=>'Yes, registering on the platform is completely free. Prize pools are set by event organisers and their distribution is solely the organiser\'s responsibility.'],
            ['q'=>'Do I need a team to compete?', 'a'=>'Yes, all tournaments on Tech Dragons Events are team-based. You can create a one-person team for solo formats or get invited by a captain.'],
            ['q'=>'How are brackets calculated?', 'a'=>'We use the standard seeding algorithm for single-elimination brackets. Seeds 1 and 2 are placed in opposite halves and can only meet in the final. Byes are handled automatically when the team count is not a power of two.'],
            ['q'=>'What happens if a team misses check-in?', 'a'=>'A team that does not confirm during the check-in window can be removed from the bracket by organisers. This ensures the tournament runs without empty slots.'],
            ['q'=>'Who reports match scores?', 'a'=>'Scores are entered by platform administrators/referees. The bracket advances automatically as soon as a result is confirmed. Self-reporting by players is not currently supported.'],
            ['q'=>'How do I report a dispute?', 'a'=>'Contact the event organiser directly via the Contact section or Discord. Tech Dragons Events provides the technical infrastructure but does not adjudicate disputes between teams.'],
            ['q'=>'Do my stats appear on the leaderboard immediately?', 'a'=>'Yes, the Leaderboard updates automatically after every completed match. You can view wins, losses, win rate, and prize earnings for every team on the dedicated page.'],
        ];
        foreach ($faqs as $fi => $faq): ?>
        <div class="faq-item">
            <button class="faq-btn" aria-expanded="false" onclick="toggleFaq(this)">
                <span><?= htmlspecialchars($faq['q'], ENT_QUOTES) ?></span>
                <span class="faq-chevron">▼</span>
            </button>
            <div class="faq-body"><?= htmlspecialchars($faq['a'], ENT_QUOTES) ?></div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- Bottom CTA -->
    <div class="reveal" style="text-align:center;margin-top:64px;padding:48px 32px;background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);">
        <h2 style="font-size:26px;font-weight:800;letter-spacing:-0.8px;margin-bottom:12px;">
            <?= $isIt ? 'Pronto a competere?' : 'Ready to compete?' ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:14px;margin-bottom:24px;">
            <?= $isIt
                ? 'Crea il tuo account gratuito e unisciti alla community di Tech Dragons Events.'
                : 'Create your free account and join the Tech Dragons Events community.' ?>
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="/register.php" class="btn-primary" style="padding:12px 28px;">
                <?= $isIt ? 'Registrati Gratis' : 'Register Free' ?>
            </a>
            <a href="/about.php" class="btn-secondary" style="padding:12px 28px;">
                <?= $isIt ? 'Chi Siamo' : 'About Us' ?>
            </a>
        </div>
    </div>

</div>
</main>

<script>
function switchTab(tab) {
    document.querySelectorAll('.guide-section').forEach(function(el) { el.style.display = 'none'; });
    document.querySelectorAll('.guide-tab').forEach(function(el) { el.classList.remove('active'); });
    document.getElementById('tab-' + tab).style.display = 'block';
    document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
}
function toggleFaq(btn) {
    var body = btn.nextElementSibling;
    var isOpen = body.classList.contains('open');
    document.querySelectorAll('.faq-body').forEach(function(a) { a.classList.remove('open'); });
    document.querySelectorAll('.faq-btn').forEach(function(b) { b.setAttribute('aria-expanded', 'false'); });
    if (!isOpen) { body.classList.add('open'); btn.setAttribute('aria-expanded', 'true'); }
}
</script>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
