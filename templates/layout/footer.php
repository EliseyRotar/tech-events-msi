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
                        <a href="/#about"><?= t('footer_features') ?></a>
                        <a href="/dashboard.php"><?= t('footer_dashboard') ?></a>
                        <a href="/assets/storici/storico.html"><?= t('footer_archive') ?></a>
                    </div>
                    <div class="footer-col">
                        <h4><?= t('footer_account') ?></h4>
                        <a href="/register.php"><?= t('footer_create_account') ?></a>
                        <a href="/login.php"><?= t('footer_signin') ?></a>
                        <a href="/addTeam.php"><?= t('footer_register_team') ?></a>
                    </div>
                    <div class="footer-col">
                        <h4><?= t('footer_games') ?></h4>
                        <a href="/assets/storici/cs2.html">CS2</a>
                        <a href="/assets/storici/valorant.html">Valorant</a>
                        <a href="/assets/storici/dota.html">Dota 2</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p><?= t('footer_copyright') ?></p>
                <div class="footer-bottom-links">
                    <a href="#"><?= t('footer_privacy') ?></a>
                    <a href="#"><?= t('footer_terms') ?></a>
                    <a href="#"><?= t('footer_status') ?></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Main JS (loads after GSAP deferred scripts) -->
    <script src="/assets/js/main.js" defer></script>
</body>
</html>
