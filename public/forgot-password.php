<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Mailer.php';

use App\Mailer;

$sent    = false;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Always show success to avoid email enumeration
        $stm = $pdo->prepare("SELECT idUtente, nome FROM utenti WHERE email = :e AND email_verified = 1");
        $stm->execute([':e' => $email]);
        $user = $stm->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $pdo->prepare(
                "UPDATE utenti SET reset_token = :t, reset_expires_at = :e WHERE idUtente = :id"
            )->execute([':t' => $token, ':e' => $expiresAt, ':id' => $user['idUtente']]);

            $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $resetUrl  = "{$scheme}://{$host}/reset-password.php?token={$token}";
            $name      = $user['nome'];

            Mailer::send($email, 'Reset your Tech Dragons password', <<<TXT
Hi {$name},

You requested a password reset for your Tech Dragons Events account.

Click the link below to set a new password:
{$resetUrl}

This link expires in 1 hour. If you didn't request this, ignore this email — your password won't change.

— Tech Dragons Events
TXT,
            <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#0a0a0a;font-family:Inter,sans-serif;color:#fff;">
<div style="max-width:560px;margin:40px auto;padding:40px;background:#111;border:1px solid rgba(255,255,255,0.08);border-radius:16px;">
  <div style="text-align:center;margin-bottom:32px;">
    <span style="font-size:22px;font-weight:700;">Tech<span style="color:#00d4ff">Dragons</span></span>
  </div>
  <h1 style="font-size:24px;font-weight:700;margin:0 0 12px;">Reset your password</h1>
  <p style="color:#888;line-height:1.7;margin:0 0 32px;">
    Hi {$name}, click the button below to reset your Tech Dragons Events password. This link is valid for <strong style="color:#ccc">1 hour</strong>.
  </p>
  <div style="text-align:center;margin-bottom:32px;">
    <a href="{$resetUrl}" style="display:inline-block;padding:14px 32px;background:#00d4ff;color:#000;font-weight:700;font-size:15px;border-radius:10px;text-decoration:none;">
      Reset Password
    </a>
  </div>
  <p style="color:#555;font-size:13px;">If you didn't request this, ignore this email.</p>
</div>
</body>
</html>
HTML
            );
        }

        $sent = true;
    }
}

$pageTitle = 'Reset Password — Tech Dragons Events';
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
        <div class="links">
            <a href="/login.php" class="btn-secondary" style="padding:8px 18px;">Sign In</a>
        </div>
    </div>
</nav>

<div class="form-page">
    <div class="form-card">
        <?php if ($sent): ?>
        <div style="text-align:center;">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                <svg width="28" height="28" viewBox="0 0 32 32" fill="none" stroke="#00d4ff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="6" width="28" height="20" rx="3"/>
                    <path d="M2 9l14 9 14-9"/>
                </svg>
            </div>
            <span class="section-label">Check your inbox</span>
            <h1 style="font-size:24px;margin-bottom:12px;">Email sent</h1>
            <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:28px;">
                If an account with that email exists, we've sent a reset link. Check your spam folder if you don't see it within a few minutes.
            </p>
            <a href="/login.php" class="btn-secondary" style="display:inline-flex;padding:11px 28px;">Back to Sign In</a>
        </div>
        <?php else: ?>
        <span class="section-label">Account Recovery</span>
        <h1>Forgot Password?</h1>
        <p class="lead">Enter your email and we'll send you a reset link.</p>

        <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input class="form-input" type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
                       placeholder="you@organization.com" required autocomplete="email">
            </div>
            <button type="submit" class="btn-primary btn-submit">Send Reset Link</button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:14px;color:var(--text-secondary);">
            Remembered it? <a href="/login.php" style="color:var(--accent-blue);font-weight:600;">Sign in</a>
        </p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
