    <footer>
        <div class="footer-inner">
            <div class="footer-top">
                <div class="footer-brand">
                    <span class="footer-logo">Tech<span>Dragons</span></span>
                    <p><?= t('footer_tagline') ?></p>
                </div>

                <div class="footer-links">
                    <div class="footer-col">
                        <h4><?= t('footer_platform') ?></h4>
                        <a href="/#events"><?= t('footer_events') ?></a>
                        <a href="/leaderboard.php"><?= t('nav_leaderboard') ?></a>
                        <a href="/news.php"><?= t('nav_news') ?></a>
                        <a href="/guide.php"><?= t('nav_guide') ?></a>
                        <a href="/about.php"><?= t('nav_about') ?></a>
                        <a href="/sponsors.php"><?= t('footer_partners') ?></a>
                        <a href="/assets/storici/storico.html"><?= t('footer_archive') ?></a>
                    </div>
                    <div class="footer-col">
                        <h4><?= t('footer_account') ?></h4>
                        <a href="/register.php"><?= t('footer_create_account') ?></a>
                        <a href="/login.php"><?= t('footer_signin') ?></a>
                        <a href="/addTeam.php"><?= t('footer_register_team') ?></a>
                        <a href="/profile.php"><?= t('nav_profile') ?></a>
                        <a href="/dashboard.php"><?= t('footer_dashboard') ?></a>
                    </div>
                    <div class="footer-col">
                        <h4><?= t('footer_games') ?></h4>
                        <a href="/assets/storici/cs2.html">CS2</a>
                        <a href="/assets/storici/valorant.html">Valorant</a>
                        <a href="/assets/storici/dota.html">Dota 2</a>
                        <a href="/assets/storici/storico.html">League of Legends</a>
                    </div>
                </div>
            </div>

            <!-- Social links row -->
            <div style="display:flex;gap:14px;align-items:center;padding:20px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);margin-bottom:20px;flex-wrap:wrap;">
                <span style="font-size:12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-right:4px;">Follow</span>
                <!-- X / Twitter -->
                <a href="https://x.com/techdragonevents" target="_blank" rel="noopener" aria-label="X (Twitter)"
                   style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:13px;font-weight:500;text-decoration:none;padding:6px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);transition:all 0.2s;"
                   onmouseover="this.style.borderColor='var(--text-primary)';this.style.color='var(--text-primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-secondary)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    X / Twitter
                </a>
                <!-- GitHub -->
                <a href="https://github.com/EliseyRotar/tech-events-msi" target="_blank" rel="noopener" aria-label="GitHub"
                   style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:13px;font-weight:500;text-decoration:none;padding:6px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);transition:all 0.2s;"
                   onmouseover="this.style.borderColor='var(--text-primary)';this.style.color='var(--text-primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-secondary)'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/></svg>
                    GitHub
                </a>
            </div>

            <div class="footer-bottom">
                <p><?= t('footer_copyright') ?></p>
                <div class="footer-bottom-links">
                    <a href="/privacy.php"><?= t('footer_privacy') ?></a>
                    <a href="/terms.php"><?= t('footer_terms') ?></a>
                    <a href="/sponsors.php"><?= t('footer_partners') ?></a>
                    <a href="https://status.render.com" target="_blank" rel="noopener"><?= t('footer_status') ?></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Main JS (loads after GSAP deferred scripts) -->
    <script src="/assets/js/main.js" defer></script>
</body>
</html>
