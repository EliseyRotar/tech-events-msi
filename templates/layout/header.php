<?php
if (isset($_GET['lang'])) {
    $supported = ['it', 'en'];
    $lang = in_array($_GET['lang'], $supported, true) ? $_GET['lang'] : 'it';
    setcookie('lang', $lang, time() + 365 * 24 * 3600, '/');
    $params = $_GET;
    unset($params['lang']);
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    if (!is_string($url) || !str_starts_with($url, '/') || str_starts_with($url, '//')) {
        $url = '/';
    }
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

// Load translations so t() is available to all pages that include this header
require_once __DIR__ . '/../../src/helpers.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_COOKIE['lang'] ?? 'en', ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Tech Dragons Events — Esports Infrastructure', ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="Professional esports event management. Tournament orchestration, team coordination, and digital ticketing.">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">

    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
</head>
<body>

<!-- Page load overlay -->
<div id="page-overlay" aria-hidden="true">
    <span class="overlay-logo">TD</span>
</div>

<!-- Custom cursor -->
<div id="cursor-dot"  aria-hidden="true"></div>
<div id="cursor-ring" aria-hidden="true"></div>

<!-- Navigation -->
<nav class="glass-nav" id="navbar" role="navigation" aria-label="Main navigation">
    <div class="nav-container">
        <a href="/" class="nav-logo" aria-label="Tech Dragons Events — Home">
            Tech<span>Dragons</span>
        </a>

        <div class="links" role="list">
            <a href="/assets/storici/storico.html" role="listitem"><?= t('nav_archive') ?></a>
            <a href="/#events" role="listitem"><?= t('nav_events') ?></a>
            <a href="/#about" role="listitem"><?= t('nav_platform') ?></a>
            <a href="/#contact" role="listitem"><?= t('nav_contact') ?></a>
            <?php if (isset($_SESSION['email'])): ?>
                <a href="/dashboard.php" class="btn-secondary" style="padding:8px 18px;"><?= t('nav_dashboard') ?></a>
                <a href="/logout.php" class="btn-ghost" style="padding:8px 18px;"><?= t('nav_signout') ?></a>
            <?php else: ?>
                <a href="/login.php" class="btn-secondary" style="padding:8px 18px;"><?= t('nav_signin') ?></a>
                <a href="/register.php" class="btn-primary" style="padding:8px 18px;"><?= t('nav_register') ?></a>
            <?php endif; ?>
        </div>

        <div class="lang-switch" aria-label="Language selector">
            <a href="?lang=it" class="lang-btn <?= (($_COOKIE['lang'] ?? 'it') === 'it') ? 'lang-active' : '' ?>" aria-label="Italian">IT</a>
            <span style="color:var(--border-bright)">/</span>
            <a href="?lang=en" class="lang-btn <?= (($_COOKIE['lang'] ?? 'it') === 'en') ? 'lang-active' : '' ?>" aria-label="English">EN</a>
        </div>

        <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- Mobile menu -->
<nav class="mobile-menu" id="mobile-menu" aria-label="Mobile navigation">
    <a href="/assets/storici/storico.html"><?= t('nav_archive') ?></a>
    <a href="/#events"><?= t('nav_events') ?></a>
    <a href="/#about"><?= t('nav_platform') ?></a>
    <a href="/#contact"><?= t('nav_contact') ?></a>
    <?php if (isset($_SESSION['email'])): ?>
        <a href="/dashboard.php"><?= t('nav_dashboard') ?></a>
        <a href="/logout.php"><?= t('nav_signout') ?></a>
    <?php else: ?>
        <a href="/login.php"><?= t('nav_signin') ?></a>
        <a href="/register.php"><?= t('nav_register') ?> →</a>
    <?php endif; ?>
</nav>
