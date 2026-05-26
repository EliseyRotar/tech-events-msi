<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$isIt = (($_COOKIE['lang'] ?? 'it') === 'it');

$pageTitle = $isIt
    ? 'Guida — Tech Dragons Events'
    : 'Guide — Tech Dragons Events';

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
.faq-body { color: var(--text-secondary); font-size: 14px; line-height: 1.7; padding-bottom: 18px; display: none; }
.faq-body.open { display: block; }
</style>

<main class="page-main">
<div class="container">

    <!-- Hero -->
    <div class="reveal" style="text-align:center;margin-bottom:64px;padding-top:8px;">
        <span class="section-label"><?= $isIt ? 'Come Funziona' : 'How It Works' ?></span>
        <h1 style="font-size:clamp(32px,5vw,56px);font-weight:800;letter-spacing:-1.5px;margin-bottom:16px;">
            <?= $isIt ? 'Guida alla Piattaforma' : 'Platform Guide' ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:16px;max-width:500px;margin:0 auto 28px;">
            <?= $isIt
                ? 'Scegli il tuo ruolo e scopri come funziona tutto, passo dopo passo.'
                : 'Pick your role and see exactly how everything works, step by step.' ?>
        </p>
    </div>

    <!-- Tab switcher -->
    <div class="reveal" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:48px;">
        <button class="guide-tab active" data-tab="players" onclick="switchTab('players')">
            🎮 <?= $isIt ? 'Giocatori' : 'Players' ?>
        </button>
        <button class="guide-tab" data-tab="captains" onclick="switchTab('captains')">
            👑 <?= $isIt ? 'Capitani' : 'Team Captains' ?>
        </button>
        <button class="guide-tab" data-tab="admins" onclick="switchTab('admins')">
            ⚙️ <?= $isIt ? 'Organizzatori' : 'Organisers' ?>
        </button>
    </div>

    <?php
    $sections = [
        'players' => [
            'label' => $isIt ? 'Per i Giocatori' : 'For Players',
            'title' => $isIt ? 'Come entrare in un torneo' : 'How to enter a tournament',
            'sub'   => $isIt ? 'Sei passaggi. Dall\'inizio alla gara.' : 'Six steps. Start to finish.',
            'steps' => $isIt ? [
                ['icon'=>'📝','title'=>'Crea un account','desc'=>'Vai su Registrati, scegli un gamertag e il gioco è fatto. È gratuito.','link'=>'/register.php','lbl'=>'Registrati →'],
                ['icon'=>'🔍','title'=>'Trova un evento','desc'=>'La pagina degli eventi mostra tutto quello che è aperto — gioco, formato, posti rimasti. Trova qualcosa che ti interessa.','link'=>'/#events','lbl'=>'Vedi gli eventi →'],
                ['icon'=>'🛡️','title'=>'Entra in un team','desc'=>'Ogni torneo è a squadre. Fatti aggiungere da un capitano oppure crea il tuo team dalla Dashboard — bastano trenta secondi.','link'=>'/addTeam.php','lbl'=>'Crea un team →'],
                ['icon'=>'📋','title'=>'Iscriviti','desc'=>'Il capitano iscrive il team al torneo dalla pagina dell\'evento. Assicurati di essere nel roster prima che i posti finiscano.','link'=>null,'lbl'=>null],
                ['icon'=>'✅','title'=>'Fai il check-in','desc'=>'Prima dell\'inizio si apre una finestra di check-in. Vai sulla pagina del torneo e confermati. Chi salta il check-in rischia di essere tolto dal bracket.','link'=>null,'lbl'=>null],
                ['icon'=>'🏆','title'=>'Gioca e segui il bracket','desc'=>'Il tuo match è sulla pagina del bracket. Cliccaci sopra per vedere avversario, orario e punteggi in diretta man mano che il torneo avanza.','link'=>null,'lbl'=>null],
                ['icon'=>'📊','title'=>'Controlla i risultati','desc'=>'Dopo il torneo trovi tutto nella Classifica: vittorie, sconfitte, win rate e montepremi vinti. Vengono aggiornati subito dopo ogni match.','link'=>'/leaderboard.php','lbl'=>'Vai alla Classifica →'],
            ] : [
                ['icon'=>'📝','title'=>'Create an account','desc'=>'Hit Register, pick a gamertag, done. It\'s free.','link'=>'/register.php','lbl'=>'Register →'],
                ['icon'=>'🔍','title'=>'Find an event','desc'=>'The Events page shows everything open — game, format, slots left. Find something that fits.','link'=>'/#events','lbl'=>'View events →'],
                ['icon'=>'🛡️','title'=>'Get on a team','desc'=>'Every tournament is team-based. Have your captain add you, or make your own team from the Dashboard — takes about 30 seconds.','link'=>'/addTeam.php','lbl'=>'Create a team →'],
                ['icon'=>'📋','title'=>'Enter the tournament','desc'=>'Your captain enters the team from the event page. Just make sure you\'re on the roster before slots fill up.','link'=>null,'lbl'=>null],
                ['icon'=>'✅','title'=>'Check in','desc'=>'Before the tournament starts, a check-in opens. Go to the tournament page and confirm. Miss it and you might get pulled from the bracket.','link'=>null,'lbl'=>null],
                ['icon'=>'🏆','title'=>'Play and follow the bracket','desc'=>'Your match is on the bracket page. Click it to see your opponent, scheduled time, and live scores as the tournament runs.','link'=>null,'lbl'=>null],
                ['icon'=>'📊','title'=>'Track your stats','desc'=>'After the tournament, your record is on the Leaderboard — wins, losses, win rate, prize money. Updates after every match.','link'=>'/leaderboard.php','lbl'=>'View Leaderboard →'],
            ],
        ],
        'captains' => [
            'label' => $isIt ? 'Per i Capitani' : 'For Team Captains',
            'title' => $isIt ? 'Come gestire un team' : 'How to manage a team',
            'sub'   => $isIt ? 'Metti insieme la tua squadra e scendi in campo.' : 'Get your squad together and compete.',
            'steps' => $isIt ? [
                ['icon'=>'👑','title'=>'Crea il team','desc'=>'Dashboard → Registra Team. Dagli un nome. Sei il capitano.','link'=>'/addTeam.php','lbl'=>'Registra team →'],
                ['icon'=>'👥','title'=>'Aggiungi i giocatori','desc'=>'Dalla pagina del team aggiungi i membri. Puoi gestire il roster, rimuovere chi non gioca più e aggiornare i ruoli quando vuoi.','link'=>null,'lbl'=>null],
                ['icon'=>'📋','title'=>'Iscriviti a un torneo','desc'=>'Vai sulla pagina di un evento, trova un torneo aperto e clicca Iscriviti. Il tuo team è dentro, se ci sono posti.','link'=>'/#events','lbl'=>'Sfoglia i tornei →'],
                ['icon'=>'✅','title'=>'Gestisci il check-in','desc'=>'Quando si apre la finestra di check-in, vai sulla pagina del torneo e conferma. Un clic e il team è a posto.','link'=>null,'lbl'=>null],
                ['icon'=>'📊','title'=>'Segui il bracket','desc'=>'Dal momento in cui il bracket viene generato, ogni risultato si aggiorna in tempo reale. I piazzamenti finali vanno direttamente in classifica.','link'=>'/leaderboard.php','lbl'=>'Classifica globale →'],
            ] : [
                ['icon'=>'👑','title'=>'Create the team','desc'=>'Dashboard → Register Team. Give it a name. You\'re the captain.','link'=>'/addTeam.php','lbl'=>'Register team →'],
                ['icon'=>'👥','title'=>'Add your players','desc'=>'From the team page, add members to the roster. You can update it, remove players, and change roles any time.','link'=>null,'lbl'=>null],
                ['icon'=>'📋','title'=>'Enter a tournament','desc'=>'Find an event, pick an open tournament, and click Enter. Your team is in, as long as slots are available.','link'=>'/#events','lbl'=>'Browse tournaments →'],
                ['icon'=>'✅','title'=>'Handle check-in','desc'=>'When the check-in window opens, go to the tournament page and confirm. One click and your team is set.','link'=>null,'lbl'=>null],
                ['icon'=>'📊','title'=>'Follow the bracket','desc'=>'Once the bracket is up, every result updates in real time. Final placements go straight into the global leaderboard.','link'=>'/leaderboard.php','lbl'=>'Global leaderboard →'],
            ],
        ],
        'admins' => [
            'label' => $isIt ? 'Per gli Organizzatori' : 'For Organisers',
            'title' => $isIt ? 'Come organizzare un torneo' : 'How to run a tournament',
            'sub'   => $isIt ? 'Niente spreadsheet. Niente catene di DM. Solo il torneo.' : 'No spreadsheets. No DM chains. Just run the tournament.',
            'steps' => $isIt ? [
                ['icon'=>'🌐','title'=>'Crea l\'evento','desc'=>'Dalla Dashboard admin crea un nuovo evento con nome, data e formato (LAN o online). L\'evento è il contenitore: dentro ci vanno i tornei.','link'=>'/dashboard.php','lbl'=>'Apri Dashboard →'],
                ['icon'=>'🏆','title'=>'Aggiungi un torneo','desc'=>'Dentro l\'evento aggiungi un torneo. Scegli il gioco, imposta il montepremi e il numero massimo di team.','link'=>null,'lbl'=>null],
                ['icon'=>'👥','title'=>'I team si iscrivono da soli','desc'=>'Le squadre si registrano autonomamente dalla pagina dell\'evento. Tu monitori la lista dalla pagina Gestisci Match.','link'=>null,'lbl'=>null],
                ['icon'=>'🌱','title'=>'Assegna i seed','desc'=>'Nella tabella Team Seeding assegna un numero a ogni team — 1 è il più forte. I seed 1 e 2 finiscono in metà bracket opposte: si incontrano solo in finale.','link'=>null,'lbl'=>null],
                ['icon'=>'⏰','title'=>'Apri il check-in','desc'=>'Clicca "Apri Finestra Check-in". Le squadre confermano la loro presenza. Chi non lo fa può essere tagliato prima che parta il bracket.','link'=>null,'lbl'=>null],
                ['icon'=>'⚡','title'=>'Genera il bracket','desc'=>'Clicca Genera Bracket. Il sistema posiziona i team per seed, costruisce tutti i round e gestisce i bye se il numero di squadre non è una potenza di 2.','link'=>null,'lbl'=>null],
                ['icon'=>'📋','title'=>'Registra i punteggi','desc'=>'Dopo ogni partita clicca Score, inserisci il risultato e seleziona Completa. Il vincitore avanza automaticamente. L\'ultimo match chiude il torneo e salva i piazzamenti.','link'=>null,'lbl'=>null],
                ['icon'=>'🔔','title'=>'Notifiche Discord','desc'=>'Incolla un webhook Discord nella sezione Notifiche. Riceverai un messaggio nel tuo server per ogni risultato, per l\'inizio del torneo e per il vincitore finale.','link'=>null,'lbl'=>null],
            ] : [
                ['icon'=>'🌐','title'=>'Create the event','desc'=>'From the admin Dashboard create an event with a name, date, and format (LAN or online). The event is the folder — tournaments go inside it.','link'=>'/dashboard.php','lbl'=>'Open Dashboard →'],
                ['icon'=>'🏆','title'=>'Add a tournament','desc'=>'Inside the event, add a tournament. Pick the game, set the prize pool, and cap the team count.','link'=>null,'lbl'=>null],
                ['icon'=>'👥','title'=>'Teams register themselves','desc'=>'Squads sign up from the event page. You just watch the list grow on the Manage Match page.','link'=>null,'lbl'=>null],
                ['icon'=>'🌱','title'=>'Set the seeds','desc'=>'In the Team Seeding table, give each team a number — 1 is your strongest. Seeds 1 and 2 go in opposite halves of the bracket. They can only meet in the final.','link'=>null,'lbl'=>null],
                ['icon'=>'⏰','title'=>'Open check-in','desc'=>'Click "Open Check-in Window". Teams confirm they\'re showing up. Anyone who misses it can be cut before the bracket drops.','link'=>null,'lbl'=>null],
                ['icon'=>'⚡','title'=>'Generate the bracket','desc'=>'Click Generate Bracket. The system places teams by seed, builds every round, and handles byes if your team count isn\'t a power of two.','link'=>null,'lbl'=>null],
                ['icon'=>'📋','title'=>'Enter scores','desc'=>'After each match, click Score, type the result, hit Complete. The winner moves on automatically. The last match closes the tournament and locks in placements.','link'=>null,'lbl'=>null],
                ['icon'=>'🔔','title'=>'Discord notifications','desc'=>'Paste a Discord webhook into the Notifications box. Your server gets a message for every result, the tournament start, and when a champion is crowned.','link'=>null,'lbl'=>null],
            ],
        ],
    ];

    foreach ($sections as $tabId => $section):
    ?>
    <div id="tab-<?= $tabId ?>" class="guide-section" <?= $tabId !== 'players' ? 'style="display:none;"' : '' ?>>
        <div style="margin-bottom:32px;" class="reveal">
            <span class="section-label"><?= $section['label'] ?></span>
            <h2 style="font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:8px;"><?= $section['title'] ?></h2>
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
            ['q'=>'È gratuito?',
             'a'=>'Sì. Creare un account, registrare un team e partecipare ai tornei non costa nulla. I montepremi li decide chi organizza l\'evento — quella parte non riguarda la piattaforma.'],
            ['q'=>'Ho bisogno di un team completo?',
             'a'=>'Devi essere in un team, ma il team può avere anche un solo membro se il formato lo permette. Di solito gli organizzatori indicano il numero minimo di giocatori per roster nella pagina del torneo.'],
            ['q'=>'Come funziona il bracket?',
             'a'=>'Eliminazione singola. I team vengono posizionati per seed — 1 e 2 finiscono nei rami opposti e possono incontrarsi solo in finale. Se il numero di team non è una potenza di 2, vengono aggiunti i bye automaticamente.'],
            ['q'=>'Cosa succede se perdiamo il check-in?',
             'a'=>'L\'organizzatore può togliervi dal bracket. La finestra di check-in esiste proprio per questo: capire chi si presenta davvero prima che parta tutto.'],
            ['q'=>'Chi inserisce i punteggi?',
             'a'=>'Gli admin della piattaforma. Per ora non c\'è la segnalazione autonoma da parte dei giocatori — i risultati passano tutti dagli organizzatori dell\'evento.'],
            ['q'=>'C\'è una disputa. Cosa faccio?',
             'a'=>'Contatta direttamente l\'organizzatore tramite la sezione Contatto o su Discord. Tech Dragons gestisce la piattaforma, non le singole competizioni — le controversie le risolve chi ha creato il torneo.'],
            ['q'=>'Le statistiche si aggiornano subito?',
             'a'=>'Sì. Appena un match viene marcato come completato, vittorie, sconfitte, win rate e premi guadagnati si aggiornano istantaneamente nella Classifica.'],
        ] : [
            ['q'=>'Is it free?',
             'a'=>'Yes. Creating an account, registering a team, and entering tournaments costs nothing. Prize pools are set by whoever runs the event — that\'s between them and the teams.'],
            ['q'=>'Do I need a full team?',
             'a'=>'You need to be in a team, but the team can have just one person if the format allows it. Organisers usually post the minimum roster size on the tournament page.'],
            ['q'=>'How does the bracket work?',
             'a'=>'Single elimination. Teams are placed by seed — seeds 1 and 2 go in opposite halves so they can only meet in the final. If the team count isn\'t a power of two, byes are added automatically.'],
            ['q'=>'What if we miss check-in?',
             'a'=>'The organiser can remove your team from the bracket. The check-in window exists so they know every slot is filled before the draw goes up.'],
            ['q'=>'Who enters the scores?',
             'a'=>'The event admins do. Self-reporting by players isn\'t supported right now — results go through whoever\'s running the tournament.'],
            ['q'=>'There\'s a dispute. What do I do?',
             'a'=>'Contact the event organiser directly via the Contact section or Discord. Tech Dragons runs the platform, not individual tournaments — disputes are handled by whoever set the event up.'],
            ['q'=>'Do stats update right away?',
             'a'=>'Yes. As soon as a match is marked complete, wins, losses, win rate, and prize money update on the Leaderboard immediately.'],
        ];
        foreach ($faqs as $faq): ?>
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

    <!-- CTA -->
    <div class="reveal" style="text-align:center;margin-top:64px;padding:48px 32px;background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);">
        <h2 style="font-size:26px;font-weight:800;letter-spacing:-0.8px;margin-bottom:12px;">
            <?= $isIt ? 'Hai capito tutto. Adesso gioca.' : 'You know how it works. Go play.' ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:14px;margin-bottom:24px;">
            <?= $isIt
                ? 'Crea un account gratuito e unisciti alla community Tech Dragons.'
                : 'Create a free account and join the Tech Dragons community.' ?>
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
