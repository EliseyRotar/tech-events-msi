<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$token = trim($_GET['token'] ?? '');
$error = null;
$done  = false;

// Validate token upfront
$user = null;
if ($token !== '') {
    $stm = $pdo->prepare(
        "SELECT idUtente, nome FROM utenti
         WHERE reset_token = :t AND reset_expires_at > NOW()"
    );
    $stm->execute([':t' => $token]);
    $user = $stm->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    $error = 'This reset link is invalid or has expired. <a href="/forgot-password.php" style="color:var(--accent-blue)">Request a new one</a>.';
}

if ($user && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pswd  = $_POST['password']  ?? '';
    $pswd2 = $_POST['password2'] ?? '';

    if (strlen($pswd) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($pswd !== $pswd2) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($pswd, PASSWORD_ARGON2ID);
        $pdo->prepare(
            "UPDATE utenti SET pswd = :h, reset_token = NULL, reset_expires_at = NULL WHERE idUtente = :id"
        )->execute([':h' => $hash, ':id' => $user['idUtente']]);
        $done = true;
    }
}

$pageTitle = 'Set New Password — Tech Dragons Events';
?>
<!DOCTYPE html>
<html lang="en">
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

<div id="page-overlay" aria-hidden="true"><span class="overlay-logo">TD</span></div>
<div id="cursor-dot"  aria-hidden="true"></div>
<div id="cursor-ring" aria-hidden="true"></div>

<nav class="glass-nav scrolled" id="navbar">
    <div class="nav-container">
        <a href="/" class="nav-logo">Tech<span>Dragons</span></a>
    </div>
</nav>

<div class="form-page">
    <div class="form-card">
        <?php if ($done): ?>
        <div style="text-align:center;">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(52,199,89,0.1);border:1px solid rgba(52,199,89,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                <svg width="28" height="28" viewBox="0 0 32 32" fill="none" stroke="#34c759" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="5 16 11 22 27 8"/>
                </svg>
            </div>
            <span class="section-label">All done</span>
            <h1 style="font-size:24px;margin-bottom:12px;">Password updated</h1>
            <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:28px;">
                Your password has been changed successfully. You can now sign in with your new password.
            </p>
            <a href="/login.php" class="btn-primary" style="display:inline-flex;padding:12px 32px;">Sign In</a>
        </div>
        <?php elseif ($error && !$user): ?>
        <div style="text-align:center;">
            <span class="section-label">Link Expired</span>
            <h1 style="font-size:24px;margin-bottom:12px;">Invalid reset link</h1>
            <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:28px;">
                <?= $error ?>
            </p>
        </div>
        <?php else: ?>
        <span class="section-label">Security</span>
        <h1>Set New Password</h1>
        <p class="lead">Hi <?= htmlspecialchars($user['nome'], ENT_QUOTES) ?>, choose a strong new password.</p>

        <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input class="form-input" type="password" id="password" name="password"
                       placeholder="••••••••" required autocomplete="new-password" minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label" for="password2">Confirm Password</label>
                <input class="form-input" type="password" id="password2" name="password2"
                       placeholder="••••••••" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn-primary btn-submit">Update Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
