<?php
session_start();
http_response_code(404);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$pageTitle = t('err_404_title') . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
<div class="container" style="text-align:center;padding:80px 20px;">

    <div class="reveal">
        <!-- Glitch 404 -->
        <div style="position:relative;display:inline-block;margin-bottom:24px;">
            <div style="font-family:var(--font-display);font-size:clamp(100px,20vw,180px);font-weight:800;letter-spacing:-8px;line-height:1;
                        background:linear-gradient(135deg,rgba(0,212,255,0.15),rgba(102,126,234,0.15));
                        -webkit-background-clip:text;color:rgba(0,212,255,0.12);
                        text-shadow:0 0 60px rgba(0,212,255,0.06);">
                404
            </div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                <div style="font-family:var(--font-display);font-size:clamp(80px,16vw,140px);font-weight:800;letter-spacing:-6px;line-height:1;
                            color:var(--accent-blue);opacity:0.08;transform:translate(-3px,2px);">
                    404
                </div>
            </div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                <div style="font-family:var(--font-display);font-size:clamp(80px,16vw,140px);font-weight:800;letter-spacing:-6px;line-height:1;
                            color:var(--accent-blue);opacity:0.25;">
                    404
                </div>
            </div>
        </div>

        <span class="section-label" style="display:block;margin-bottom:16px;"><?= t('err_404_label') ?></span>
        <h1 style="font-size:clamp(24px,4vw,42px);font-weight:800;letter-spacing:-1px;margin-bottom:16px;">
            <?= t('err_404_title') ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:16px;max-width:440px;margin:0 auto 40px;line-height:1.65;">
            <?= t('err_404_sub') ?>
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="/" class="btn-primary" style="padding:13px 28px;"><?= t('err_404_home') ?></a>
            <a href="/#events" class="btn-secondary" style="padding:13px 28px;"><?= t('err_404_events') ?></a>
            <a href="/news.php" class="btn-secondary" style="padding:13px 28px;"><?= t('err_404_news') ?></a>
        </div>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
