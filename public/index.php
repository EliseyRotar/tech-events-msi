<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$pageTitle = 'Tech Dragons Events — The Infrastructure for Professional Competition';

// Fetch events with their top tournament + game for the events section
$eventsStm = $pdo->query(
    "SELECT
        e.idEvento, e.nome, e.citta, e.paese, e.dataInizio, e.dataFine, e.nPosti,
        (SELECT t.montePremi FROM tornei t WHERE t.idEvento = e.idEvento ORDER BY t.montePremi DESC LIMIT 1) AS montePremi,
        (SELECT g.nomeGioco FROM giochi g JOIN tornei t ON g.idGioco = t.idGioco WHERE t.idEvento = e.idEvento LIMIT 1) AS nomeGioco
     FROM evento e
     ORDER BY e.dataInizio DESC
     LIMIT 9"
);
$events = $eventsStm ? $eventsStm->fetchAll(PDO::FETCH_ASSOC) : [];

require_once __DIR__ . '/../templates/layout/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     1. HERO
═══════════════════════════════════════════════════════ -->
<section class="hero" id="home">
    <!-- WebGL animated background -->
    <canvas id="hero-canvas" aria-hidden="true"></canvas>

    <!-- CSS fallback (shown only if WebGL unavailable) -->
    <div class="hero-visuals mesh-fallback" aria-hidden="true" style="display:none;">
        <div class="mesh-container">
            <div class="mesh-orb orb-1"></div>
            <div class="mesh-orb orb-2"></div>
            <div class="mesh-orb orb-3"></div>
        </div>
        <div class="grid-overlay"></div>
    </div>

    <!-- Grid overlay sits on top of canvas for depth -->
    <div class="grid-overlay" aria-hidden="true"></div>

    <!-- Content -->
    <div class="hero-content">
        <div class="hero-badge"><?= t('hero_badge') ?></div>

        <h1>
            <span class="hero-word">The</span>
            <span class="hero-word">Infrastructure</span>
            <span class="hero-word">for</span><br>
            <span class="hero-word gradient-text">Professional</span>
            <span class="hero-word gradient-text">Competition</span>
        </h1>

        <p class="hero-sub">
            Deploy a comprehensive platform for tournament orchestration, team coordination,
            and digital ticketing. Built for organisations that compete at the highest level.
        </p>

        <div class="hero-actions">
            <a href="#events" class="btn-primary">Explore Events</a>
            <a href="/register.php" class="btn-secondary">Register Now</a>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="scroll-indicator" aria-hidden="true">
        <span>Scroll</span>
        <div class="scroll-indicator-line"></div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     2. STATS BAR
═══════════════════════════════════════════════════════ -->
<section class="stats-bar" aria-label="Platform statistics">
    <div class="stats-grid">
        <div class="stat-item reveal">
            <span class="stat-number" data-count="250" data-suffix="+">250+</span>
            <span class="stat-label">Events Managed</span>
        </div>
        <div class="stat-item reveal">
            <span class="stat-number" data-count="48" data-suffix="">48</span>
            <span class="stat-label">Countries</span>
        </div>
        <div class="stat-item reveal">
            <span class="stat-number" data-count="2" data-prefix="€" data-suffix="M+">€2M+</span>
            <span class="stat-label">Prize Pools Managed</span>
        </div>
        <div class="stat-item reveal">
            <span class="stat-number" data-count="12000" data-suffix="">12,000</span>
            <span class="stat-label">Registered Athletes</span>
        </div>
    </div>

    <!-- Scrolling ticker -->
    <div class="ticker-wrap" aria-hidden="true">
        <div class="ticker-track">
            <span class="ticker-item">CS2 Championships</span>
            <span class="ticker-item">Valorant Masters</span>
            <span class="ticker-item">Dota 2 International</span>
            <span class="ticker-item">League of Legends World Cup</span>
            <span class="ticker-item">Rainbow Six Pro League</span>
            <span class="ticker-item">Apex Legends Global Series</span>
            <span class="ticker-item">Rocket League Championship</span>
            <span class="ticker-item">Overwatch League</span>
            <!-- duplicate for seamless loop -->
            <span class="ticker-item">CS2 Championships</span>
            <span class="ticker-item">Valorant Masters</span>
            <span class="ticker-item">Dota 2 International</span>
            <span class="ticker-item">League of Legends World Cup</span>
            <span class="ticker-item">Rainbow Six Pro League</span>
            <span class="ticker-item">Apex Legends Global Series</span>
            <span class="ticker-item">Rocket League Championship</span>
            <span class="ticker-item">Overwatch League</span>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     3. EVENTS
═══════════════════════════════════════════════════════ -->
<section class="events-section" id="events">
    <div class="events-header">
        <div class="events-title">
            <span class="section-label">Live Competitions</span>
            <h2>Active Events</h2>
        </div>
        <div class="filter-tabs" role="tablist" aria-label="Event type filter">
            <button class="filter-tab active" data-filter="all"  role="tab" aria-selected="true">All</button>
            <button class="filter-tab"         data-filter="lan"    role="tab" aria-selected="false">LAN</button>
            <button class="filter-tab"         data-filter="online" role="tab" aria-selected="false">Online</button>
        </div>
    </div>

    <div class="events-grid" role="list">
        <?php if (empty($events)): ?>
        <div class="events-empty">
            <h3>No events scheduled yet</h3>
            <p>Check back soon — the next competition is always around the corner.</p>
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                <a href="/createEvent.php" class="btn-primary" style="margin-top:20px;display:inline-flex;">Create First Event</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <?php foreach ($events as $ev):
            $hasCity  = !empty(trim((string)($ev['citta'] ?? '')));
            $type     = $hasCity ? 'lan' : 'online';
            $typeLabel = $hasCity ? 'LAN' : 'Online';
            $gameTag  = htmlspecialchars($ev['nomeGioco'] ?? 'Multi-title', ENT_QUOTES);
            $prize    = $ev['montePremi'] ? '€' . number_format((float)$ev['montePremi'], 0, '.', ',') : null;
            $location = trim(implode(', ', array_filter([
                htmlspecialchars($ev['citta'] ?? '', ENT_QUOTES),
                htmlspecialchars($ev['paese'] ?? '', ENT_QUOTES),
            ])));
            $dateStart = $ev['dataInizio'] ? date('M j', strtotime($ev['dataInizio'])) : '';
            $dateEnd   = $ev['dataFine']   ? date('M j, Y', strtotime($ev['dataFine'])) : '';
        ?>
        <article class="event-card" data-type="<?= $type ?>" role="listitem">
            <div class="event-card-top">
                <span class="event-tag"><?= $gameTag ?></span>
                <span class="event-type-badge"><?= $typeLabel ?></span>
            </div>

            <h3 class="event-name"><?= htmlspecialchars($ev['nome'], ENT_QUOTES) ?></h3>

            <div class="event-meta">
                <?php if ($dateStart): ?>
                <div class="event-meta-row">
                    <svg class="event-meta-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="1" y="3" width="14" height="12" rx="2"/>
                        <path d="M5 1v4M11 1v4M1 7h14"/>
                    </svg>
                    <?= $dateStart ?><?= $dateEnd ? ' — ' . $dateEnd : '' ?>
                </div>
                <?php endif; ?>

                <?php if ($location): ?>
                <div class="event-meta-row">
                    <svg class="event-meta-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="8" cy="6" r="3"/>
                        <path d="M8 1C5.24 1 3 3.24 3 6c0 4 5 9 5 9s5-5 5-9c0-2.76-2.24-5-5-5z"/>
                    </svg>
                    <?= $location ?>
                </div>
                <?php endif; ?>

                <div class="event-meta-row">
                    <svg class="event-meta-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M8 1a7 7 0 1 1 0 14A7 7 0 0 1 8 1z"/>
                        <path d="M8 4.5v4l2.5 2.5"/>
                    </svg>
                    <?= (int)$ev['nPosti'] ?> slots available
                </div>
            </div>

            <div class="event-footer">
                <?php if ($prize): ?>
                    <span class="event-prize"><?= $prize ?><small>prize pool</small></span>
                <?php else: ?>
                    <span class="event-prize" style="color:var(--text-secondary);font-size:14px;">TBA</span>
                <?php endif; ?>
                <a href="/dashboard.php?id=<?= (int)$ev['idEvento'] ?>" class="btn-primary" style="padding:8px 16px;font-size:13px;">
                    View Details
                </a>
            </div>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     4. ABOUT / PLATFORM FEATURES
═══════════════════════════════════════════════════════ -->
<section class="about-section" id="about">
    <div class="about-inner">
        <!-- Left: big statement -->
        <div class="about-left">
            <span class="section-label">The Platform</span>
            <p class="about-statement">
                <span class="about-word">Built</span>
                <span class="about-word">for</span>
                <span class="about-word">organisations</span>
                <span class="about-word">that</span>
                <span class="about-word">refuse</span>
                <span class="about-word">to</span>
                <span class="about-word">compromise</span>
                <span class="about-word">on</span>
                <span class="about-word">infrastructure.</span>
            </p>
            <p class="about-lead">
                Tech Dragons Events powers the full lifecycle of professional competition — from bracket creation to broadcast-ready data feeds.
                Every component is designed to operate at global scale without a single point of failure.
            </p>
            <a href="/register.php" class="btn-primary">Get Started Free</a>
        </div>

        <!-- Right: feature blocks -->
        <div class="about-right">
            <div class="feature-block">
                <div class="feature-block-icon">🛡️</div>
                <div class="feature-block-content">
                    <h3>RBAC Security</h3>
                    <p>Role-based access control with Argon2ID password hashing. Separate permission layers for admins, organizers, and players — no privilege escalation possible.</p>
                </div>
            </div>

            <div class="feature-block">
                <div class="feature-block-icon">🏆</div>
                <div class="feature-block-content">
                    <h3>Tournament Orchestration</h3>
                    <p>Create, schedule, and manage tournaments linked to events. Assign game titles, set prize pools, and coordinate multi-bracket competitions from a single interface.</p>
                </div>
            </div>

            <div class="feature-block">
                <div class="feature-block-icon">👥</div>
                <div class="feature-block-content">
                    <h3>Professional Organisations</h3>
                    <p>Full roster management with sponsor integration. Track members, assign in-game roles, and link athlete profiles to verified user accounts.</p>
                </div>
            </div>

            <div class="feature-block">
                <div class="feature-block-icon">📺</div>
                <div class="feature-block-content">
                    <h3>Broadcast Ready</h3>
                    <p>Live API endpoints for overlay integration with Twitch, YouTube, and professional broadcast toolchains. Real-time data, zero latency.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     5. ORGANIZERS / TEAM SECTION
═══════════════════════════════════════════════════════ -->
<section class="organizers-section" id="organizers">
    <div class="organizers-header reveal">
        <span class="section-label">The Team</span>
        <h2>Trusted by Elite Organisations</h2>
        <p>From regional upstarts to established franchises, the world's top teams run their operations on Tech Dragons Events.</p>
    </div>

    <div class="organizers-grid">
        <!-- Profile cards — flip on hover to show bio -->
        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face">
                    <div class="card-avatar">AK</div>
                    <span class="card-name">Alex Kowalski</span>
                    <span class="card-role">Head of Operations</span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Alex Kowalski</span>
                    <span class="card-role">Head of Operations</span>
                    <p class="card-bio">10 years in esports event logistics. Managed 40+ LAN events across Europe and North America with a combined 80,000 attendees.</p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face">
                    <div class="card-avatar">SR</div>
                    <span class="card-name">Sofia Reyes</span>
                    <span class="card-role">Tournament Director</span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Sofia Reyes</span>
                    <span class="card-role">Tournament Director</span>
                    <p class="card-bio">Architect of 3 international circuit series. Specialises in Swiss format brackets and multi-region qualifier systems.</p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face">
                    <div class="card-avatar">NM</div>
                    <span class="card-name">Nikita Morozov</span>
                    <span class="card-role">Platform Engineer</span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Nikita Morozov</span>
                    <span class="card-role">Platform Engineer</span>
                    <p class="card-bio">Built the core tournament engine. Maintains 99.98% uptime across 12 simultaneous major events. Former backend lead at ESL Gaming.</p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face">
                    <div class="card-avatar">LP</div>
                    <span class="card-name">Lena Park</span>
                    <span class="card-role">Partnerships Lead</span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Lena Park</span>
                    <span class="card-role">Partnerships Lead</span>
                    <p class="card-bio">Manages sponsor relationships with 30+ global brands. Structured €1.2M in prize pool sponsorships across 2024 circuit events.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     6. CONTACT / REGISTER
═══════════════════════════════════════════════════════ -->
<section class="contact-section" id="contact">
    <div class="contact-inner">
        <div class="contact-header reveal">
            <span class="section-label">Get Involved</span>
            <h2>Ready to Compete?</h2>
            <p>Whether you're a player, organizer, or sponsor — we want to hear from you.</p>
        </div>

        <!-- Contact form -->
        <div id="contact-form-wrap">
            <form class="contact-form" id="contact-form" novalidate>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="c-name">Full Name</label>
                        <input class="form-input" type="text" id="c-name" name="name" placeholder="Your name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="c-email">Email Address</label>
                        <input class="form-input" type="email" id="c-email" name="email" placeholder="you@organization.com" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="c-org">Organisation</label>
                        <input class="form-input" type="text" id="c-org" name="organization" placeholder="Team / Company name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="c-role">Your Role</label>
                        <select class="form-select" id="c-role" name="role">
                            <option value="">Select a role…</option>
                            <option value="player">Player</option>
                            <option value="organizer">Event Organizer</option>
                            <option value="sponsor">Sponsor</option>
                            <option value="broadcast">Broadcaster</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-msg">Message</label>
                    <textarea class="form-textarea" id="c-msg" name="message" placeholder="Tell us about your event, team, or inquiry…" rows="5"></textarea>
                </div>

                <div class="form-submit-wrap">
                    <button type="submit" class="btn-primary btn-submit">Send Message</button>
                </div>
            </form>

            <!-- Success state -->
            <div id="form-success" class="form-success" role="alert" aria-live="polite">
                <svg class="success-check" viewBox="0 0 52 52" aria-hidden="true">
                    <circle cx="26" cy="26" r="25"/>
                    <path d="M14 27l8 8 16-16"/>
                </svg>
                <h3>Message Received</h3>
                <p>We'll be in touch within 24 hours. In the meantime, create your account to get started.</p>
                <a href="/register.php" class="btn-primary" style="margin-top:8px;">Create Account</a>
            </div>
        </div>
    </div>
</section>

<script src="/assets/js/hero-bg.js"></script>
<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
