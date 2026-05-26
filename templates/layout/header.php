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

    <?php
    $metaDesc = 'Tech Dragons Events — professional esports tournament platform. Create and join LAN and online competitions, manage team rosters, track prize pools. Free to register.';
    $metaDescIt = 'Tech Dragons Events — piattaforma professionale per tornei esports. Crea e partecipa a competizioni LAN e online, gestisci roster, monitorizza prize pool. Registrazione gratuita.';
    $isIt = (($_COOKIE['lang'] ?? 'it') === 'it');
    $canonicalBase = 'https://tech-events-msi.onrender.com';
    $canonicalPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $canonical = $canonicalBase . $canonicalPath;
    $ogTitle = htmlspecialchars($pageTitle ?? 'Tech Dragons Events — Esports Infrastructure', ENT_QUOTES);
    $ogDesc  = $isIt ? $metaDescIt : $metaDesc;
    ?>
    <meta name="description" content="<?= $isIt ? htmlspecialchars($metaDescIt, ENT_QUOTES) : htmlspecialchars($metaDesc, ENT_QUOTES) ?>">
    <meta name="keywords" content="esports, tournament, LAN, online competition, team management, prize pool, gaming events, tech dragons, torneo esports, competizione online">
    <meta name="author" content="Tech Dragons Events">
    <meta name="robots" content="index, follow">
    <meta name="google-site-verification" content="sRhwlFc-7CJMLbBnAGhE4HyV8a5bIkPtOyQrk2OYhKc">
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES) ?>">

    <!-- Open Graph (Facebook, LinkedIn, Discord, WhatsApp) -->
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="Tech Dragons Events">
    <meta property="og:title"       content="<?= $ogTitle ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDesc, ENT_QUOTES) ?>">
    <meta property="og:url"         content="<?= htmlspecialchars($canonical, ENT_QUOTES) ?>">
    <meta property="og:image"       content="<?= $canonicalBase ?>/assets/img/logo.jpg">
    <meta property="og:image:width"  content="500">
    <meta property="og:image:height" content="553">
    <meta property="og:locale"      content="<?= $isIt ? 'it_IT' : 'en_GB' ?>">
    <meta property="og:locale:alternate" content="<?= $isIt ? 'en_GB' : 'it_IT' ?>">

    <!-- Twitter / X Card -->
    <meta name="twitter:card"        content="summary">
    <meta name="twitter:title"       content="<?= $ogTitle ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDesc, ENT_QUOTES) ?>">
    <meta name="twitter:image"       content="<?= $canonicalBase ?>/assets/img/logo.jpg">

    <!-- hreflang for bilingual pages -->
    <link rel="alternate" hreflang="it" href="<?= htmlspecialchars($canonicalBase . $canonicalPath . '?lang=it', ENT_QUOTES) ?>">
    <link rel="alternate" hreflang="en" href="<?= htmlspecialchars($canonicalBase . $canonicalPath . '?lang=en', ENT_QUOTES) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($canonical, ENT_QUOTES) ?>">

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "https://tech-events-msi.onrender.com/#organization",
          "name": "Tech Dragons Events",
          "url": "https://tech-events-msi.onrender.com",
          "logo": "https://tech-events-msi.onrender.com/assets/img/logo.png",
          "description": "Professional esports event management platform for tournaments, teams, and competitions.",
          "foundingDate": "2026",
          "email": "techdragonevents@gmail.com",
          "sameAs": [
            "https://github.com/EliseyRotar/tech-events-msi"
          ]
        },
        {
          "@type": "WebSite",
          "@id": "https://tech-events-msi.onrender.com/#website",
          "url": "https://tech-events-msi.onrender.com",
          "name": "Tech Dragons Events",
          "publisher": {"@id": "https://tech-events-msi.onrender.com/#organization"},
          "inLanguage": ["it", "en"],
          "potentialAction": {
            "@type": "RegisterAction",
            "target": "https://tech-events-msi.onrender.com/register.php",
            "name": "Join the platform"
          }
        },
        {
          "@type": "SportsOrganization",
          "name": "Tech Dragons Events",
          "sport": "Esports",
          "url": "https://tech-events-msi.onrender.com",
          "logo": "https://tech-events-msi.onrender.com/assets/img/logo.png"
        }
      ]
    }
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon"  href="/favicon.ico">
    <link rel="icon" type="image/png"     href="/favicon-32.png" sizes="32x32">
    <link rel="apple-touch-icon"          href="/apple-touch-icon.png">

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

    <!-- Three.js (for scrollytelling 3D scene on home page) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js" defer></script>
</head>
<body>

<!-- Page load overlay -->
<div id="page-overlay" aria-hidden="true">
    <img src="/assets/img/logo.png" class="overlay-logo-img" alt="Tech Dragons Events">
</div>

<!-- Custom cursor -->
<div id="cursor-dot"  aria-hidden="true"></div>
<div id="cursor-ring" aria-hidden="true"></div>

<!-- Navigation -->
<nav class="glass-nav" id="navbar" role="navigation" aria-label="Main navigation">
    <div class="nav-container">
        <a href="/" class="nav-logo" aria-label="Tech Dragons Events — Home">
            <img src="/assets/img/logo.png" alt="Tech Dragons" class="nav-logo-img">
            Tech<span>Dragons</span>
        </a>

        <div class="links" role="list">
            <a href="/assets/storici/storico.html" role="listitem"><?= t('nav_archive') ?></a>
            <a href="/#events" role="listitem"><?= t('nav_events') ?></a>
            <a href="/leaderboard.php" role="listitem"><?= t('nav_leaderboard') ?></a>
            <a href="/news.php" role="listitem"><?= t('nav_news') ?></a>
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
    <a href="/leaderboard.php"><?= t('nav_leaderboard') ?></a>
    <a href="/news.php"><?= t('nav_news') ?></a>
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
