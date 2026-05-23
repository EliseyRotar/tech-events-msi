<?php
require_once '../config.php';
require_once '../src/helpers.php';
require_once '../src/Mailer.php';

use App\Mailer;

$sent   = false;
$error  = '';
$prefill = htmlspecialchars(trim($_GET['email'] ?? ''), ENT_QUOTES);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stm = $pdo->prepare(
            "SELECT idUtente, nome, email_verified FROM utenti WHERE email = :e LIMIT 1"
        );
        $stm->execute([':e' => $email]);
        $row = $stm->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Don't reveal whether the email exists — show success regardless
            $sent = true;
        } elseif ((int)$row['email_verified'] === 1) {
            // Already verified — send to login
            header('Location: /login.php');
            exit;
        } else {
            // Issue a fresh token (rate-limit: only if previous token is older than 2 min)
            $check = $pdo->prepare(
                "SELECT token_expires_at FROM utenti WHERE idUtente = :id"
            );
            $check->execute([':id' => $row['idUtente']]);
            $existing = $check->fetchColumn();

            $cooldownOk = true;
            if ($existing) {
                $issued  = (new DateTime($existing))->modify('-24 hours +2 minutes');
                $cooldownOk = new DateTime() > $issued;
            }

            if ($cooldownOk) {
                $token     = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 86400);
                $pdo->prepare(
                    "UPDATE utenti SET verification_token = :tok, token_expires_at = :exp WHERE idUtente = :id"
                )->execute([':tok' => $token, ':exp' => $expiresAt, ':id' => $row['idUtente']]);

                $appUrl    = rtrim(getenv('APP_URL') ?: 'https://tech-events-msi.onrender.com', '/');
                $verifyUrl = "{$appUrl}/verify.php?token={$token}";
                $name      = $row['nome'] ?? 'there';

                $text = "Hi {$name},\n\nHere's your new verification link:\n\n{$verifyUrl}\n\nThis link expires in 24 hours.\n\n— Tech Dragons Events";
                $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#0a0a0a;font-family:Inter,sans-serif;color:#fff;">
<div style="max-width:560px;margin:40px auto;padding:40px;background:#111;border:1px solid rgba(255,255,255,0.08);border-radius:16px;">
  <div style="text-align:center;margin-bottom:32px;">
    <span style="font-size:22px;font-weight:700;">Tech<span style="color:#00d4ff;">Dragons</span></span>
  </div>
  <h1 style="font-size:22px;font-weight:700;margin:0 0 12px;">New verification link</h1>
  <p style="color:#888;line-height:1.7;margin:0 0 28px;">Hi {$name}, here's your new email verification link.</p>
  <div style="text-align:center;margin-bottom:28px;">
    <a href="{$verifyUrl}" style="display:inline-block;padding:14px 32px;background:#00d4ff;color:#000;font-weight:700;font-size:15px;border-radius:10px;text-decoration:none;">
      Verify Email Address
    </a>
  </div>
  <p style="color:#555;font-size:13px;">Expires in 24 hours.<br>
  <span style="color:#00d4ff;word-break:break-all;">{$verifyUrl}</span></p>
</div>
</body></html>
HTML;
                Mailer::send($email, 'New verification link — Tech Dragons Events', $text, $html);
            }
            $sent = true; // Always show success
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_COOKIE['lang'] ?? 'en', ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification — Tech Dragons Events</title>
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
        <div class="links">
            <a href="/login.php" class="btn-secondary" style="padding:8px 18px;">Sign In</a>
        </div>
    </div>
</nav>

<div class="form-page">
    <div class="form-card" style="<?= $sent ? 'text-align:center;' : '' ?>">

    <?php if ($sent): ?>
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="#00d4ff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="6" width="28" height="20" rx="3"/>
                <path d="M2 9l14 9 14-9"/>
            </svg>
        </div>
        <span class="section-label">Email sent</span>
        <h1 style="font-size:24px;margin-bottom:12px;">Check your inbox</h1>
        <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:28px;">
            If that email belongs to an unverified account, we've sent a fresh verification link. Check your spam folder if you don't see it.
        </p>
        <a href="/login.php" class="btn-secondary" style="display:inline-flex;padding:11px 28px;">Back to Sign In</a>

    <?php else: ?>
        <span class="section-label">Email verification</span>
        <h1>Resend verification</h1>
        <p class="lead">Enter your email address and we'll send you a new verification link.</p>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input class="form-input" type="email" id="email" name="email"
                       value="<?= $prefill ?>"
                       placeholder="you@organization.com" required autocomplete="email">
            </div>
            <button type="submit" class="btn-primary btn-submit">Send verification email</button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:14px;color:var(--text-secondary);">
            <a href="/login.php" style="color:var(--accent-blue);">← Back to Sign In</a>
        </p>
    <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
