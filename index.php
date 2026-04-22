<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EEM — Esports Event Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav>
        <a href="index.html" class="nav-logo">EEM</a>
        <div class="links">
            <a href="storici/storico.html">Storico</a>
            <a href="#platform">Piattaforma</a>
            <a href="#tournaments">Tornei</a>
            <a href="#tickets">Biglietti</a>
            <a href="#organizers">Organizer</a>
            <a href="../login.php" class="btn-nav">Accedi</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-grid"></div>
        <div class="hero-content">
            <div class="hero-badge">Piattaforma Esports</div>
            <h1>Gestisci Eventi<br><span class="gradient-text">Esports</span><br>come un Pro</h1>
            <p>Una piattaforma completa per creare eventi, organizzare tornei con bracket automatici e vendere biglietti in pochi click.</p>
            <div class="hero-actions">
                <a href="../createEvent.php" class="btn-primary">Crea un Evento →</a>
                <a href="storici/storico.html" class="btn-ghost">Vedi lo Storico</a>
            </div>
        </div>
    </section>

    <!-- MARQUEE -->
    <div class="section-marquee">
        <div class="marquee-inner">
            <span>Tournament Bracket</span><span class="dot">✦</span>
            <span>Event Booking</span><span class="dot">✦</span>
            <span>Live Streaming</span><span class="dot">✦</span>
            <span>QR Ticketing</span><span class="dot">✦</span>
            <span>Team Management</span><span class="dot">✦</span>
            <span>Real-time Results</span><span class="dot">✦</span>
            <span>Admin Panel</span><span class="dot">✦</span>
            <span>Tournament Bracket</span><span class="dot">✦</span>
            <span>Event Booking</span><span class="dot">✦</span>
            <span>Live Streaming</span><span class="dot">✦</span>
            <span>QR Ticketing</span><span class="dot">✦</span>
            <span>Team Management</span><span class="dot">✦</span>
            <span>Real-time Results</span><span class="dot">✦</span>
            <span>Admin Panel</span><span class="dot">✦</span>
        </div>
    </div>

    <!-- PLATFORM -->
    <section id="platform">
        <p class="section-label">La Piattaforma</p>
        <p class="big-statement">EEM è il gestionale pensato per <span class="gradient-text">organizer</span>, team e community competitive. Dalla creazione dell'evento alle statistiche finali.</p>
    </section>

    <!-- FEATURES -->
    <section>
        <p class="section-label">Funzionalità</p>
        <h2>Tutto quello che ti serve</h2>
        <p class="section-sub">Ogni strumento per gestire un evento esports dal primo all'ultimo minuto.</p>
        <div class="features-grid">
            <div class="feature">
                <span class="feature-icon">📅</span>
                <h3>Event Booking</h3>
                <p>Crea e pianifica eventi online o LAN in modo semplice.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">🏆</span>
                <h3>Tournament Bracket</h3>
                <p>Bracket automatici, round robin, eliminazione diretta.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">🎟</span>
                <h3>Ticketing</h3>
                <p>Vendita biglietti digitale con QR code e check-in rapido.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">👥</span>
                <h3>Team & Player</h3>
                <p>Registrazione team, roster e check-in automatico.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">📺</span>
                <h3>Streaming Ready</h3>
                <p>Overlay compatibili con Twitch & YouTube.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">⚙️</span>
                <h3>Admin Panel</h3>
                <p>Pannello centralizzato per gestire tutto il tuo evento.</p>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat">
                <span class="stat-number">500+</span>
                <p>Eventi creati</p>
            </div>
            <div class="stat">
                <span class="stat-number">20K+</span>
                <p>Player registrati</p>
            </div>
            <div class="stat">
                <span class="stat-number">99.9%</span>
                <p>Uptime piattaforma</p>
            </div>
        </div>
    </section>

    <!-- TOURNAMENTS -->
    <section id="tournaments">
        <p class="section-label">Tornei</p>
        <h2>Gestione Tornei</h2>
        <p class="section-sub">Bracket, risultati e streaming in un unico pannello.</p>
        <div class="cards-grid">
            <div class="card">
                <h3>Bracket Dinamici</h3>
                <p>Aggiornamento in tempo reale dei match.</p>
            </div>
            <div class="card">
                <h3>Score & Result</h3>
                <p>Inserimento risultati automatico o manuale.</p>
            </div>
            <div class="card">
                <h3>Streaming Ready</h3>
                <p>Overlay compatibili con Twitch & YouTube.</p>
            </div>
        </div>
    </section>

    <!-- ORGANIZERS -->
    <section id="organizers">
        <p class="section-label">Come Funziona</p>
        <h2>Per Organizer</h2>
        <p class="section-sub">4 passi per portare il tuo evento al livello successivo.</p>
        <div class="timeline">
            <div class="timeline-item">
                <span class="timeline-num">01</span>
                <div>
                    <h3>Crea Evento</h3>
                    <p>Definisci gioco, formato, date e regole.</p>
                </div>
            </div>
            <div class="timeline-item">
                <span class="timeline-num">02</span>
                <div>
                    <h3>Apri Iscrizioni</h3>
                    <p>Team e player si registrano in autonomia.</p>
                </div>
            </div>
            <div class="timeline-item">
                <span class="timeline-num">03</span>
                <div>
                    <h3>Gestisci Torneo</h3>
                    <p>Bracket, risultati, admin panel centralizzato.</p>
                </div>
            </div>
            <div class="timeline-item">
                <span class="timeline-num">04</span>
                <div>
                    <h3>Live & Post Event</h3>
                    <p>Streaming, classifiche e statistiche finali.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- TICKETS -->
    <section id="tickets">
        <p class="section-label">Biglietteria</p>
        <h2>Biglietti & Accessi</h2>
        <p class="section-sub">Dalla vendita online al check-in fisico, tutto automatizzato.</p>
        <div class="cards-grid">
            <div class="card">
                <h3>Vendita Online</h3>
                <p>Biglietti digitali con accesso rapido.</p>
            </div>
            <div class="card">
                <h3>QR Check-in</h3>
                <p>Scansione rapida all'ingresso evento.</p>
            </div>
            <div class="card">
                <h3>Analytics</h3>
                <p>Dashboard su vendite, presenze e revenue.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section" id="contact">
        <div class="cta-content">
            <p class="section-label">Inizia Ora</p>
            <h2>Ready to Play?</h2>
            <p>Porta i tuoi eventi esports al livello successivo.</p>
            <div class="hero-actions" style="justify-content:center; margin-top:40px;">
                <a href="../createEvent.php" class="btn-primary">Crea il tuo Evento →</a>
                <a href="../sign_in.php" class="btn-ghost">Registrati gratis</a>
            </div>
        </div>
    </section>

    <footer>
        <span class="footer-logo">EEM</span>
        <p>© 2026 — Tech Dragons Events</p>
    </footer>

</body>
</html>
