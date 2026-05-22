<?php
require_once '../config.php';
require_once '../src/helpers.php';

$pageTitle = t('login_title') . ' — Tech Dragons Events';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['emailTxt'] ?? '');
    $password = trim($_POST['pswdTxt'] ?? '');

    $stm = $pdo->prepare("SELECT idUtente, pswd, isAdmin FROM utenti WHERE email = :e");
    $stm->bindParam(':e', $email);
    $stm->execute();
    $credentials = $stm->fetch(PDO::FETCH_ASSOC);

    if ($credentials && password_verify($password, $credentials['pswd'])) {
        session_start();
        $_SESSION['email']  = $email;
        $_SESSION['id']     = $credentials['idUtente'];
        $_SESSION['accept'] = 'ACCEPT';
        $_SESSION['admin']  = $credentials['isAdmin'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_COOKIE['lang'] ?? 'en', ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body class="page-form">

<!-- Overlay -->
<div id="page-overlay" aria-hidden="true"><span class="overlay-logo">TD</span></div>
<div id="cursor-dot"  aria-hidden="true"></div>
<div id="cursor-ring" aria-hidden="true"></div>

<!-- Nav -->
<nav class="glass-nav scrolled" id="navbar">
    <div class="nav-container">
        <a href="/" class="nav-logo">Tech<span>Dragons</span></a>
        <div class="links">
            <a href="/">Overview</a>
            <a href="/register.php" class="btn-primary" style="padding:8px 18px;"><?= t('nav_register') ?></a>
        </div>
        <div class="lang-switch">
            <a href="?lang=it" class="lang-btn <?= (($_COOKIE['lang'] ?? 'it') === 'it') ? 'lang-active' : '' ?>">IT</a>
            <span style="color:var(--border-bright)">/</span>
            <a href="?lang=en" class="lang-btn <?= (($_COOKIE['lang'] ?? 'it') === 'en') ? 'lang-active' : '' ?>">EN</a>
        </div>
    </div>
</nav>

<!-- Form page -->
<div class="form-page">
    <div class="form-card">
        <span class="section-label"><?= t('login_label') ?></span>
        <h1><?= t('login_title') ?></h1>
        <p class="lead"><?= t('login_lead') ?></p>

        <?php if ($registered): ?>
            <div class="error-msg" style="background:rgba(0,232,120,0.08);border-color:rgba(0,232,120,0.3);color:var(--success);">
                Account created successfully — sign in to continue.
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="emailTxt"><?= t('login_email') ?></label>
                <input class="form-input" type="email" id="emailTxt" name="emailTxt"
                       value="<?= htmlspecialchars($_POST['emailTxt'] ?? '', ENT_QUOTES) ?>"
                       placeholder="name@organization.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="pswdTxt"><?= t('login_password') ?></label>
                <input class="form-input" type="password" id="pswdTxt" name="pswdTxt"
                       placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-primary btn-submit"><?= t('login_submit') ?></button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:14px;color:var(--text-secondary);">
            <?= t('login_register_prompt') ?>
            <a href="/register.php" style="color:var(--accent-blue);font-weight:600;"><?= t('login_register_link') ?></a>
        </p>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
