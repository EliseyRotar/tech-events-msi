<?php
require_once '../src/helpers.php';
$pageTitle = t('terms_page_title');
?>
<?php include '../templates/layout/header.php'; ?>

<main class="legal-page">
    <div class="legal-container">

        <div class="legal-header">
            <span class="section-label"><?= t('footer_terms') ?></span>
            <h1><?= t('terms_title') ?></h1>
            <p class="legal-updated"><?= t('terms_updated') ?></p>
        </div>

        <div class="legal-intro">
            <p><?= t('terms_intro') ?></p>
        </div>

        <div class="legal-body">

            <section class="legal-section">
                <h2><?= t('terms_s1_title') ?></h2>
                <p><?= t('terms_s1_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('terms_s2_title') ?></h2>
                <p><?= t('terms_s2_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('terms_s3_title') ?></h2>
                <p><?= t('terms_s3_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('terms_s4_title') ?></h2>
                <p><?= t('terms_s4_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('terms_s5_title') ?></h2>
                <p><?= t('terms_s5_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('terms_s6_title') ?></h2>
                <p><?= t('terms_s6_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('terms_s7_title') ?></h2>
                <p><?= t('terms_s7_body') ?></p>
            </section>

            <section class="legal-section">
                <h2><?= t('terms_s8_title') ?></h2>
                <p><?= t('terms_s8_body') ?></p>
            </section>

        </div>

        <div class="legal-footer-nav">
            <a href="/privacy.php" class="btn-ghost">&larr; <?= t('footer_privacy') ?></a>
            <a href="/" class="btn-secondary"><?= t('nav_events') ?> &rarr;</a>
        </div>

    </div>
</main>

<?php include '../templates/layout/footer.php'; ?>
