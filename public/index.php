<?php
session_start();
require_once __DIR__ . '/../config.php';

$pageTitle = "Tech Events — Professional Esports Management";
require_once __DIR__ . '/../templates/layout/header.php';
?>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-visuals">
            <div class="mesh-container">
                <div class="mesh-orb orb-1"></div>
                <div class="mesh-orb orb-2"></div>
                <div class="mesh-orb orb-3"></div>
            </div>
            <div class="grid-overlay"></div>
            <div class="noise-overlay"></div>
        </div>
        <div class="hero-content">
            <div class="hero-badge">Esports Infrastructure</div>
            <h1>The Professional Standard for <br><span class="gradient-text">Event Management</span></h1>
            <p>Deploy a comprehensive platform for tournament organization, team coordination, and digital ticketing in minutes.</p>
            <div class="hero-actions">
                <a href="/createEvent.php" class="btn-primary">Get Started — Free</a>
                <a href="/assets/storici/storico.html" class="btn-secondary">View Case Studies</a>
            </div>
        </div>
    </section>

    <!-- FEATURES BENTO -->
    <section id="features" style="padding: 100px 0;">
        <p class="section-label">Capabilities</p>
        <h2>Engineered for Scale</h2>
        <div class="features-grid">
            <div class="feature">
                <span class="feature-icon">📊</span>
                <h3>Advanced Bracketing</h3>
                <p>Support for Single/Double Elimination, Round Robin, and Swiss formats with automated seeding.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">🎟️</span>
                <h3>Digital Ticketing</h3>
                <p>Enterprise-grade ticketing system with encrypted QR codes and real-time gate management.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">🛡️</span>
                <h3>Secure Authentication</h3>
                <p>Argon2ID password hashing and role-based access control (RBAC) for organizers and players.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">👥</span>
                <h3>Team Management</h3>
                <p>Full-cycle roster management, including sponsor integration and player verification.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">📺</span>
                <h3>Broadcast Ready</h3>
                <p>Live API endpoints for broadcast overlays on Twitch, YouTube, and specialized platforms.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">⚙️</span>
                <h3>Admin Control</h3>
                <p>Centralized command center for managing schedules, disputes, and live analytics.</p>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat">
                <span class="stat-number">500+</span>
                <p>Events Managed</p>
            </div>
            <div class="stat">
                <span class="stat-number">20K+</span>
                <p>Registered Athletes</p>
            </div>
            <div class="stat">
                <span class="stat-number">99.9%</span>
                <p>Platform Uptime</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section" style="padding: 120px 24px; text-align: center;">
        <div class="cta-content" style="max-width: 800px; margin: 0 auto; background: var(--surface); border: 1px solid var(--border); padding: 60px; border-radius: 12px;">
            <p class="section-label">Next Steps</p>
            <h2 style="margin-bottom: 24px;">Ready to Elevate Your Competition?</h2>
            <p style="color: var(--text-muted); margin-bottom: 40px;">Join the elite organizations using Tech Events to power their esports ecosystems.</p>
            <div class="hero-actions">
                <a href="/sign_in.php" class="btn-primary">Create Organization Account</a>
                <a href="/#tournaments" class="btn-secondary">Request Demo</a>
            </div>
        </div>
    </section>

<?php
require_once __DIR__ . '/../templates/layout/footer.php';
?>
