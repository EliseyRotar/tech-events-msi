<?php
session_start();
require_once '../config.php';
require_once '../src/helpers.php';

$token  = trim($_GET['token'] ?? '');
$status = 'invalid'; // invalid | expired | already | ok
$name   = '';

if ($token !== '' && strlen($token) === 64 && ctype_xdigit($token)) {
    $stm = $pdo->prepare(
        "SELECT idUtente, nome, email, email_verified, token_expires_at
         FROM utenti
         WHERE verification_token = :tok
         LIMIT 1"
    );
    $stm->execute([':tok' => $token]);
    $row = $stm->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $status = 'invalid';
    } elseif ((int)$row['email_verified'] === 1) {
        $status = 'already';
        $name   = $row['nome'];
    } elseif ($row['token_expires_at'] && new DateTime() > new DateTime($row['token_expires_at'])) {
        $status = 'expired';
        $name   = $row['nome'];
    } else {
        // Activate account
        $pdo->prepare(
            "UPDATE utenti
             SET email_verified = 1, verification_token = NULL, token_expires_at = NULL
             WHERE idUtente = :id"
        )->execute([':id' => $row['idUtente']]);

        $status = 'ok';
        $name   = $row['nome'];

        // Log the user in immediately
        $_SESSION['email']  = $row['email'];
        $_SESSION['id']     = $row['idUtente'];
        $_SESSION['accept'] = 'ACCEPT';
        $_SESSION['admin']  = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_COOKIE['lang'] ?? 'en', ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification — Tech Dragons Events</title>
    <?php if ($status === 'ok'): ?>
    <meta http-equiv="refresh" content="3;url=/dashboard.php">
    <?php endif; ?>
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
    <div class="form-card" style="text-align:center;">

        <?php if ($status === 'ok'): ?>
        <!-- ── Success ── -->
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(0,232,120,0.1);border:1px solid rgba(0,232,120,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="#00e878" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 16l7 7 13-13"/>
            </svg>
        </div>
        <span class="section-label">Verified</span>
        <h1 style="font-size:26px;margin-bottom:12px;">You're in, <?= htmlspecialchars($name, ENT_QUOTES) ?>!</h1>
        <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:32px;">
            Your email address has been verified. Your account is now active.<br>
            Redirecting you to the dashboard in a moment…
        </p>
        <a href="/dashboard.php" class="btn-primary" style="display:inline-flex;padding:13px 32px;">Go to Dashboard →</a>

        <?php elseif ($status === 'already'): ?>
        <!-- ── Already verified ── -->
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="#00d4ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="16" cy="16" r="13"/>
                <path d="M16 10v6l4 4"/>
            </svg>
        </div>
        <span class="section-label">Already verified</span>
        <h1 style="font-size:26px;margin-bottom:12px;">Account already active</h1>
        <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:32px;">
            <?= htmlspecialchars($name, ENT_QUOTES) ?>, your account is already verified.<br>
            You can sign in normally.
        </p>
        <a href="/login.php" class="btn-primary" style="display:inline-flex;padding:13px 32px;">Sign In</a>

        <?php elseif ($status === 'expired'): ?>
        <!-- ── Expired ── -->
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,149,0,0.1);border:1px solid rgba(255,149,0,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="#ff9500" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="16" cy="16" r="13"/>
                <path d="M16 9v8M16 22v1"/>
            </svg>
        </div>
        <span class="section-label">Link expired</span>
        <h1 style="font-size:26px;margin-bottom:12px;">Verification link expired</h1>
        <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:32px;">
            Your verification link has expired (links are valid for 24 hours).<br>
            Request a new one below.
        </p>
        <a href="/resend-verification.php" class="btn-primary" style="display:inline-flex;padding:13px 32px;">Resend verification email</a>

        <?php else: ?>
        <!-- ── Invalid ── -->
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,59,48,0.1);border:1px solid rgba(255,59,48,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="#ff3b30" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 10l12 12M22 10L10 22"/>
            </svg>
        </div>
        <span class="section-label">Invalid link</span>
        <h1 style="font-size:26px;margin-bottom:12px;">Verification link invalid</h1>
        <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:32px;">
            This verification link is invalid or has already been used.<br>
            If you need a new one, you can request it below.
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="/resend-verification.php" class="btn-primary" style="display:inline-flex;padding:13px 28px;">Resend email</a>
            <a href="/register.php" class="btn-secondary" style="display:inline-flex;padding:13px 28px;">Create new account</a>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
