<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$pageTitle = t('title_home');

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

// Real platform stats from DB
$statsRow = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM evento)                              AS eventCount,
        (SELECT COUNT(*) FROM utenti WHERE email_verified = 1)    AS userCount,
        (SELECT COALESCE(SUM(montePremi), 0) FROM tornei)         AS totalPrize,
        (SELECT COUNT(*) FROM squadre)                             AS teamCount"
)->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/layout/header.php';

// Build hero headline word spans
$line1 = t('hero_line1');
$line2 = t('hero_line2');
$words1 = array_filter(explode(' ', $line1));
$words2 = array_filter(explode(' ', $line2));
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
            <?php foreach ($words1 as $word): ?>
                <span class="hero-word"><?= htmlspecialchars($word, ENT_QUOTES) ?></span>
            <?php endforeach; ?><br>
            <?php foreach ($words2 as $word): ?>
                <span class="hero-word gradient-text"><?= htmlspecialchars($word, ENT_QUOTES) ?></span>
            <?php endforeach; ?>
        </h1>

        <p class="hero-sub"><?= t('hero_sub') ?></p>

        <div class="hero-actions">
            <a href="#events" class="btn-primary"><?= t('hero_cta_events') ?></a>
            <a href="/register.php" class="btn-secondary"><?= t('hero_cta_register') ?></a>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="scroll-indicator" aria-hidden="true">
        <span><?= t('hero_scroll') ?></span>
        <div class="scroll-indicator-line"></div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     2. STATS BAR
═══════════════════════════════════════════════════════ -->
<section class="stats-bar" aria-label="Platform statistics">
    <?php
    $evCount   = (int)($statsRow['eventCount'] ?? 0);
    $prize     = (float)($statsRow['totalPrize'] ?? 0);
    $teamCount = (int)($statsRow['teamCount'] ?? 0);
    $userCount = (int)($statsRow['userCount'] ?? 0);
    $prizeK    = $prize >= 1000 ? round($prize / 1000) . 'K' : (int)$prize;
    ?>
    <div class="stats-grid">
        <div class="stat-item reveal">
            <span class="stat-number" data-count="<?= $evCount ?>" data-suffix=""><?= $evCount ?></span>
            <span class="stat-label"><?= t('stat_events_label') ?></span>
        </div>
        <div class="stat-item reveal">
            <span class="stat-number" data-count="<?= $teamCount ?>" data-suffix=""><?= $teamCount ?></span>
            <span class="stat-label"><?= t('stat_teams_label') ?></span>
        </div>
        <div class="stat-item reveal">
            <span class="stat-number" data-count="<?= (int)($prize / max($prize, 1) * $prize) ?>" data-prefix="€" data-suffix=""><?= $prize > 0 ? '€' . $prizeK : '—' ?></span>
            <span class="stat-label"><?= t('stat_prizes_label') ?></span>
        </div>
        <div class="stat-item reveal">
            <span class="stat-number" data-count="<?= $userCount ?>" data-suffix=""><?= $userCount ?></span>
            <span class="stat-label"><?= t('stat_athletes_label') ?></span>
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
     2.5 SCROLLYTELLING — Enter the Arena
═══════════════════════════════════════════════════════ -->
<section class="scrollytell" id="story" aria-label="Tech Dragons story">
    <!-- Pinned 3D canvas + chrome -->
    <div class="scrollytell-sticky">
        <canvas id="story-canvas" aria-hidden="true"></canvas>
        <div class="scrollytell-grid" aria-hidden="true"></div>
        <div class="scrollytell-vignette" aria-hidden="true"></div>
        <div class="scrollytell-noise" aria-hidden="true"></div>

        <!-- Top chrome: section label + progress -->
        <div class="story-chrome story-chrome-top">
            <span class="section-label"><?= t('story_section_label') ?></span>
            <h2 class="story-section-title"><?= t('story_section_title') ?></h2>
        </div>

        <div class="story-chrome story-chrome-bottom">
            <div class="story-progress">
                <span class="story-progress-label">
                    <?= t('story_act_label') ?>
                    <span id="story-act-num">1</span>
                    <span class="story-progress-sep">/</span>
                    <span class="story-progress-total">4</span>
                </span>
                <div class="story-progress-track"><div class="story-progress-fill"></div></div>
            </div>
        </div>
    </div>

    <!-- Acts (drive the scroll) -->
    <div class="story-acts">
        <article class="story-act" data-act="1">
            <div class="story-text">
                <span class="story-chapter">01 — Origin</span>
                <h3><?= t('story_t1') ?></h3>
                <p><?= t('story_b1') ?></p>
            </div>
        </article>

        <article class="story-act story-act-right" data-act="2">
            <div class="story-text">
                <span class="story-chapter">02 — Network</span>
                <h3><?= t('story_t2') ?></h3>
                <p><?= t('story_b2') ?></p>
            </div>
        </article>

        <article class="story-act" data-act="3">
            <div class="story-text">
                <span class="story-chapter">03 — Scale</span>
                <h3><?= t('story_t3') ?></h3>
                <p><?= t('story_b3') ?></p>
            </div>
        </article>

        <article class="story-act story-act-right" data-act="4">
            <div class="story-text">
                <span class="story-chapter">04 — Stage</span>
                <h3><?= t('story_t4') ?></h3>
                <p><?= t('story_b4') ?></p>
                <a href="/register.php" class="btn-primary story-cta"><?= t('story_cta') ?> →</a>
            </div>
        </article>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     3. EVENTS
═══════════════════════════════════════════════════════ -->
<section class="events-section" id="events">
    <div class="events-header">
        <div class="events-title">
            <span class="section-label"><?= t('events_label') ?></span>
            <h2><?= t('events_title') ?></h2>
        </div>
        <div class="filter-tabs" role="tablist" aria-label="Event type filter">
            <button class="filter-tab active" data-filter="all"  role="tab" aria-selected="true"><?= t('filter_all') ?></button>
            <button class="filter-tab"         data-filter="lan"    role="tab" aria-selected="false"><?= t('filter_lan') ?></button>
            <button class="filter-tab"         data-filter="online" role="tab" aria-selected="false"><?= t('filter_online') ?></button>
        </div>
    </div>

    <div class="events-grid" role="list">
        <?php if (empty($events)): ?>
        <div class="events-empty">
            <h3><?= t('event_empty_title') ?></h3>
            <p><?= t('event_empty_sub') ?></p>
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                <a href="/createEvent.php" class="btn-primary" style="margin-top:20px;display:inline-flex;"><?= t('event_create_first') ?></a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <?php foreach ($events as $ev):
            $hasCity  = !empty(trim((string)($ev['citta'] ?? '')));
            $type     = $hasCity ? 'lan' : 'online';
            $typeLabel = $hasCity ? t('filter_lan') : t('filter_online');
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
                    <?= (int)$ev['nPosti'] ?> <?= t('event_slots') ?>
                </div>
            </div>

            <div class="event-footer">
                <?php if ($prize): ?>
                    <span class="event-prize"><?= $prize ?><small><?= t('event_prize_pool') ?></small></span>
                <?php else: ?>
                    <span class="event-prize" style="color:var(--text-secondary);font-size:14px;"><?= t('event_tba') ?></span>
                <?php endif; ?>
                <a href="/event.php?id=<?= (int)$ev['idEvento'] ?>" class="btn-primary" style="padding:8px 16px;font-size:13px;">
                    <?= t('event_view') ?>
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
            <span class="section-label"><?= t('about_label') ?></span>
            <p class="about-statement">
                <?php foreach (array_filter(explode(' ', t('about_statement'))) as $w): ?>
                    <span class="about-word"><?= htmlspecialchars($w, ENT_QUOTES) ?></span>
                <?php endforeach; ?>
            </p>
            <p class="about-lead"><?= t('about_lead') ?></p>
            <a href="/register.php" class="btn-primary"><?= t('about_cta') ?></a>
        </div>

        <!-- Right: feature blocks -->
        <div class="about-right">
            <div class="feature-block">
                <div class="feature-block-icon">🛡️</div>
                <div class="feature-block-content">
                    <h3><?= t('feature_1_title') ?></h3>
                    <p><?= t('feature_1_desc') ?></p>
                </div>
            </div>

            <div class="feature-block">
                <div class="feature-block-icon">🏆</div>
                <div class="feature-block-content">
                    <h3><?= t('feature_2_title') ?></h3>
                    <p><?= t('feature_2_desc') ?></p>
                </div>
            </div>

            <div class="feature-block">
                <div class="feature-block-icon">👥</div>
                <div class="feature-block-content">
                    <h3><?= t('feature_3_title') ?></h3>
                    <p><?= t('feature_3_desc') ?></p>
                </div>
            </div>

            <div class="feature-block">
                <div class="feature-block-icon">📺</div>
                <div class="feature-block-content">
                    <h3><?= t('feature_4_title') ?></h3>
                    <p><?= t('feature_4_desc') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     5. TEAM SECTION
═══════════════════════════════════════════════════════ -->
<section class="organizers-section" id="organizers">
    <div class="organizers-header reveal">
        <span class="section-label"><?= t('team_label') ?></span>
        <h2><?= t('team_title') ?></h2>
        <p><?= t('team_sub') ?></p>
    </div>

    <div class="organizers-grid organizers-grid-5">
        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face card-front">
                    <div class="card-avatar">ER</div>
                    <span class="card-name">Elisey Rotar</span>
                    <span class="card-role"><?= t('member_er_role') ?></span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Elisey Rotar</span>
                    <span class="card-role"><?= t('member_er_role') ?></span>
                    <p class="card-bio"><?= t('member_er_bio') ?></p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face card-front">
                    <div class="card-avatar">FD</div>
                    <span class="card-name">Francesco Daminelli</span>
                    <span class="card-role"><?= t('member_fd_role') ?></span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Francesco Daminelli</span>
                    <span class="card-role"><?= t('member_fd_role') ?></span>
                    <p class="card-bio"><?= t('member_fd_bio') ?></p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face card-front">
                    <div class="card-avatar">AV</div>
                    <span class="card-name">Andrea Valente</span>
                    <span class="card-role"><?= t('member_av_role') ?></span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Andrea Valente</span>
                    <span class="card-role"><?= t('member_av_role') ?></span>
                    <p class="card-bio"><?= t('member_av_bio') ?></p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face card-front">
                    <div class="card-avatar">AT</div>
                    <span class="card-name">Aimen Tafihi</span>
                    <span class="card-role"><?= t('member_at_role') ?></span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Aimen Tafihi</span>
                    <span class="card-role"><?= t('member_at_role') ?></span>
                    <p class="card-bio"><?= t('member_at_bio') ?></p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-inner">
                <div class="card-face card-front">
                    <div class="card-avatar">MG</div>
                    <span class="card-name">Manuel Greco</span>
                    <span class="card-role"><?= t('member_mg_role') ?></span>
                </div>
                <div class="card-face card-back">
                    <span class="card-name">Manuel Greco</span>
                    <span class="card-role"><?= t('member_mg_role') ?></span>
                    <p class="card-bio"><?= t('member_mg_bio') ?></p>
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
            <span class="section-label"><?= t('contact_label') ?></span>
            <h2><?= t('contact_title') ?></h2>
            <p><?= t('contact_sub') ?></p>
        </div>

        <!-- Contact form -->
        <div id="contact-form-wrap">
            <form class="contact-form" id="contact-form" novalidate>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="c-name"><?= t('contact_name') ?></label>
                        <input class="form-input" type="text" id="c-name" name="name" placeholder="<?= t('contact_name') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="c-email"><?= t('contact_email') ?></label>
                        <input class="form-input" type="email" id="c-email" name="email" placeholder="you@organization.com" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="c-org"><?= t('contact_org') ?></label>
                        <input class="form-input" type="text" id="c-org" name="organization" placeholder="Team / Company">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="c-role"><?= t('contact_role') ?></label>
                        <select class="form-select" id="c-role" name="role">
                            <option value=""><?= t('contact_role_opt') ?></option>
                            <option value="player"><?= t('contact_role_player') ?></option>
                            <option value="organizer"><?= t('contact_role_org') ?></option>
                            <option value="sponsor"><?= t('contact_role_sponsor') ?></option>
                            <option value="broadcast"><?= t('contact_role_broadcast') ?></option>
                            <option value="other"><?= t('contact_role_other') ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="c-msg"><?= t('contact_message') ?></label>
                    <textarea class="form-textarea" id="c-msg" name="message" placeholder="<?= t('contact_msg_ph') ?>" rows="5"></textarea>
                </div>

                <div class="form-submit-wrap">
                    <button type="submit" class="btn-primary btn-submit" data-send-label="<?= t('contact_send') ?>" data-sending-label="<?= t('contact_sending') ?>">
                        <?= t('contact_send') ?>
                    </button>
                </div>
            </form>

            <!-- Success state -->
            <div id="form-success" class="form-success" role="alert" aria-live="polite">
                <svg class="success-check" viewBox="0 0 52 52" aria-hidden="true">
                    <circle cx="26" cy="26" r="25"/>
                    <path d="M14 27l8 8 16-16"/>
                </svg>
                <h3><?= t('contact_success_title') ?></h3>
                <p><?= t('contact_success_sub') ?></p>
                <a href="/register.php" class="btn-primary" style="margin-top:8px;"><?= t('contact_success_cta') ?></a>
            </div>
        </div>
    </div>
</section>

<script src="/assets/js/hero-bg.js"></script>
<script src="/assets/js/scrollytelling.js" defer></script>
<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
