<?php
require_once '../src/helpers.php';
$pageTitle = t('privacy_page_title');
?>
<?php include '../templates/layout/header.php'; ?>

<main class="legal-page">
    <div class="legal-container">

        <div class="legal-header">
            <span class="section-label"><?= t('footer_privacy') ?></span>
            <h1><?= t('privacy_title') ?></h1>
            <p class="legal-updated"><?= t('privacy_updated') ?></p>
        </div>

        <div class="legal-intro">
            <p><?= t('privacy_intro') ?></p>
        </div>

        <div class="legal-body">

            <section class="legal-section">
                <h2><?= t('privacy_s1_title') ?></h2>
                <p><?= t('privacy_s1_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('privacy_s2_title') ?></h2>
                <p><?= t('privacy_s2_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('privacy_s3_title') ?></h2>
                <p><?= t('privacy_s3_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('privacy_s4_title') ?></h2>
                <p><?= t('privacy_s4_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('privacy_s5_title') ?></h2>
                <p><?= t('privacy_s5_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('privacy_s6_title') ?></h2>
                <p><?= t('privacy_s6_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('privacy_s7_title') ?></h2>
                <p><?= t('privacy_s7_body') ?></p>
            </section>

        </div>

        <div class="legal-footer-nav">
            <a href="/" class="btn-ghost">&larr; <?= t('nav_events') ?></a>
            <a href="/terms.php" class="btn-secondary"><?= t('footer_terms') ?> &rarr;</a>
        </div>

    </div>
</main>

<?php include '../templates/layout/footer.php'; ?>
