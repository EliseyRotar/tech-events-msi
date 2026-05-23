<?php
require_once '../config.php';
require_once '../src/helpers.php';
require_once '../src/Mailer.php';

use App\Mailer;

// Lang switch
if (isset($_GET['lang'])) {
    $supported = ['it', 'en'];
    $lang = in_array($_GET['lang'], $supported, true) ? $_GET['lang'] : 'it';
    setcookie('lang', $lang, time() + 365 * 24 * 3600, '/');
    header('Location: /register.php');
    exit;
}

$pageTitle = t('register_title') . ' — Tech Dragons Events';
$errors    = [];
$pending   = false; // show "check your email" state

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['emailTxt']    ?? '');
    $name     = trim($_POST['nameTxt']     ?? '');
    $surname  = trim($_POST['surnameTxt']  ?? '');
    $username = trim($_POST['usernameTxt'] ?? '');
    $date     = trim($_POST['dateTxt']     ?? '');
    $pswd     = $_POST['pswdTxt'] ?? '';

    // Validate required fields
    if ($name === '' || $surname === '' || $email === '' || $username === '' || $date === '' || $pswd === '') {
        $errors[] = t('err_missing_fields');
    }
    if ($username !== '' && !preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $username)) {
        $errors[] = t('err_invalid_username');
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('err_invalid_email');
    }
    if ($date !== '' && empty($errors)) {
        $dob = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dob || $dob->format('Y-m-d') !== $date) {
            $errors[] = t('err_invalid_date');
        } else {
            $now = new DateTime();
            if ($dob > $now) {
                $errors[] = t('err_future_date');
            } elseif ($now->diff($dob)->y < 13) {
                $errors[] = t('err_min_age');
            }
        }
    }
    if ($pswd !== '' && strlen($pswd) < 8) {
        $errors[] = t('err_pswd_length');
    }

    // Check duplicate email
    if (empty($errors) && $email !== '') {
        $check = $pdo->prepare("SELECT idUtente FROM utenti WHERE email = :e");
        $check->execute([':e' => $email]);
        if ($check->fetch()) {
            $errors[] = t('err_email_taken');
        }
    }
    // Check duplicate username
    if (empty($errors) && $username !== '') {
        $check2 = $pdo->prepare("SELECT idUtente FROM utenti WHERE username = :u");
        $check2->execute([':u' => $username]);
        if ($check2->fetch()) {
            $errors[] = t('err_username_taken');
        }
    }

    // Insert with unverified status
    if (empty($errors)) {
        try {
            $token     = bin2hex(random_bytes(32)); // 64-char hex token
            $expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24 hours
            $hash      = password_hash($pswd, PASSWORD_ARGON2ID);

            $pdo->beginTransaction();
            $stm = $pdo->prepare(
                "INSERT INTO utenti (username, nome, cognome, dataNascita, isAdmin, email, pswd,
                                    email_verified, verification_token, token_expires_at)
                 VALUES (:c, :n, :s, :d, 0, :e, :h, 0, :tok, :exp)"
            );
            $stm->execute([
                ':c'   => $username,
                ':n'   => $name,
                ':s'   => $surname,
                ':d'   => $date,
                ':e'   => $email,
                ':h'   => $hash,
                ':tok' => $token,
                ':exp' => $expiresAt,
            ]);
            $pdo->commit();

            // Build verification URL
            $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $verifyUrl = "{$scheme}://{$host}/verify.php?token={$token}";

            // Send verification email
            $subject = 'Verify your Tech Dragons Events account';
            $text    = <<<TXT
Hi {$name},

Welcome to Tech Dragons Events!

Click the link below to verify your email address and activate your account:

{$verifyUrl}

This link expires in 24 hours.

If you didn't create this account, you can safely ignore this email.

— Tech Dragons Events
TXT;
            $html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#0a0a0a;font-family:Inter,sans-serif;color:#ffffff;">
<div style="max-width:560px;margin:40px auto;padding:40px;background:#111111;border:1px solid rgba(255,255,255,0.08);border-radius:16px;">
  <div style="text-align:center;margin-bottom:32px;">
    <span style="font-size:22px;font-weight:700;letter-spacing:-0.5px;">Tech<span style="color:#00d4ff;">Dragons</span></span>
  </div>
  <h1 style="font-size:24px;font-weight:700;margin:0 0 12px;letter-spacing:-0.5px;">Verify your email</h1>
  <p style="color:#888888;line-height:1.7;margin:0 0 32px;">
    Hi {$name}, welcome to Tech Dragons Events. Click the button below to verify your email address and activate your account.
  </p>
  <div style="text-align:center;margin-bottom:32px;">
    <a href="{$verifyUrl}" style="display:inline-block;padding:14px 32px;background:#00d4ff;color:#000000;font-weight:700;font-size:15px;border-radius:10px;text-decoration:none;letter-spacing:-0.2px;">
      Verify Email Address
    </a>
  </div>
  <p style="color:#555555;font-size:13px;line-height:1.6;margin:0;">
    This link expires in <strong style="color:#888888;">24 hours</strong>. If you didn't create this account, ignore this email.<br><br>
    Or copy this URL into your browser:<br>
    <span style="color:#00d4ff;word-break:break-all;">{$verifyUrl}</span>
  </p>
</div>
</body>
</html>
HTML;

            Mailer::send($email, $subject, $text, $html);

            $pending = true; // Show "check your email" screen

        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = t('err_registration');
        }
    }
}
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

<div id="page-overlay" aria-hidden="true"><span class="overlay-logo">TD</span></div>
<div id="cursor-dot"  aria-hidden="true"></div>
<div id="cursor-ring" aria-hidden="true"></div>

<nav class="glass-nav scrolled" id="navbar">
    <div class="nav-container">
        <a href="/" class="nav-logo">Tech<span>Dragons</span></a>
        <div class="links">
            <a href="/">Overview</a>
            <a href="/login.php" class="btn-secondary" style="padding:8px 18px;"><?= t('nav_signin') ?></a>
        </div>
        <div class="lang-switch">
            <a href="?lang=it" class="lang-btn <?= (($_COOKIE['lang'] ?? 'it') === 'it') ? 'lang-active' : '' ?>">IT</a>
            <span style="color:var(--border-bright)">/</span>
            <a href="?lang=en" class="lang-btn <?= (($_COOKIE['lang'] ?? 'it') === 'en') ? 'lang-active' : '' ?>">EN</a>
        </div>
    </div>
</nav>

<div class="form-page">
<?php if ($pending): ?>
    <!-- ── Email sent confirmation state ── -->
    <div class="form-card" style="text-align:center;">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="#00d4ff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="6" width="28" height="20" rx="3"/>
                <path d="M2 9l14 9 14-9"/>
            </svg>
        </div>
        <span class="section-label">One more step</span>
        <h1 style="font-size:26px;margin-bottom:12px;">Check your email</h1>
        <p style="color:var(--text-secondary);line-height:1.7;margin-bottom:32px;">
            We sent a verification link to<br>
            <strong style="color:var(--text-primary);"><?= htmlspecialchars($_POST['emailTxt'] ?? '', ENT_QUOTES) ?></strong><br><br>
            Click the button in the email to activate your account. The link expires in <strong>24 hours</strong>.
        </p>

        <div style="background:rgba(0,212,255,0.05);border:1px solid rgba(0,212,255,0.15);border-radius:10px;padding:16px;margin-bottom:24px;font-size:13px;color:var(--text-secondary);text-align:left;line-height:1.7;">
            <strong style="color:var(--text-primary);">Didn't receive it?</strong><br>
            Check your spam folder. If it's not there after a few minutes,
            <a href="/resend-verification.php?email=<?= urlencode($_POST['emailTxt'] ?? '') ?>" style="color:var(--accent-blue);font-weight:600;">resend the verification email</a>.
        </div>

        <a href="/login.php" class="btn-secondary" style="display:inline-flex;padding:11px 28px;">Back to Sign In</a>
    </div>

<?php else: ?>
    <!-- ── Registration form ── -->
    <div class="form-card form-card-wide">
        <span class="section-label"><?= t('register_label') ?></span>
        <h1><?= t('register_title') ?></h1>
        <p class="lead"><?= t('register_lead') ?></p>

        <?php if (!empty($errors)): ?>
            <div class="error-msg">
                <?php if (count($errors) === 1): ?>
                    <?= htmlspecialchars($errors[0], ENT_QUOTES) ?>
                <?php else: ?>
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="nameTxt"><?= t('register_firstname') ?></label>
                    <input class="form-input" type="text" id="nameTxt" name="nameTxt"
                           value="<?= htmlspecialchars($_POST['nameTxt'] ?? '', ENT_QUOTES) ?>"
                           placeholder="John" required autocomplete="given-name">
                </div>
                <div class="form-group">
                    <label class="form-label" for="surnameTxt"><?= t('register_lastname') ?></label>
                    <input class="form-input" type="text" id="surnameTxt" name="surnameTxt"
                           value="<?= htmlspecialchars($_POST['surnameTxt'] ?? '', ENT_QUOTES) ?>"
                           placeholder="Doe" required autocomplete="family-name">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="emailTxt"><?= t('register_email') ?></label>
                <input class="form-input" type="email" id="emailTxt" name="emailTxt"
                       value="<?= htmlspecialchars($_POST['emailTxt'] ?? '', ENT_QUOTES) ?>"
                       placeholder="john@organization.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="usernameTxt"><?= t('register_username') ?></label>
                <input class="form-input" type="text" id="usernameTxt" name="usernameTxt"
                       value="<?= htmlspecialchars($_POST['usernameTxt'] ?? '', ENT_QUOTES) ?>"
                       placeholder="DragonSlayer99" required autocomplete="username"
                       pattern="[a-zA-Z0-9_\-]{3,30}">
                <small style="color:var(--text-secondary);font-size:12px;margin-top:4px;display:block;">3–30 characters. Letters, numbers, _ and - only.</small>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="dateTxt"><?= t('register_dob') ?></label>
                    <input class="form-input" type="date" id="dateTxt" name="dateTxt"
                           value="<?= htmlspecialchars($_POST['dateTxt'] ?? '', ENT_QUOTES) ?>"
                           required autocomplete="bday">
                </div>
                <div class="form-group pswd-group">
                    <label class="form-label" for="pswdTxt"><?= t('register_password') ?></label>
                    <input class="form-input" type="password" id="pswdTxt" name="pswdTxt"
                           placeholder="••••••••" required autocomplete="new-password"
                           style="margin-bottom:8px;">
                    <div class="pswd-meter" id="pswd-meter" aria-hidden="true">
                        <div class="pswd-meter-bar" id="bar-1"></div>
                        <div class="pswd-meter-bar" id="bar-2"></div>
                        <div class="pswd-meter-bar" id="bar-3"></div>
                        <div class="pswd-meter-bar" id="bar-4"></div>
                    </div>
                    <div class="pswd-label" id="pswd-label"></div>
                    <div class="pswd-reqs" id="pswd-reqs">
                        <span class="pswd-req" id="req-len">8+ characters</span>
                        <span class="pswd-req" id="req-upper">Uppercase letter</span>
                        <span class="pswd-req" id="req-lower">Lowercase letter</span>
                        <span class="pswd-req" id="req-number">Number</span>
                        <span class="pswd-req" id="req-special">Special character</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-submit"><?= t('register_submit') ?></button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:14px;color:var(--text-secondary);">
            <?= t('register_signin_prompt') ?>
            <a href="/login.php" style="color:var(--accent-blue);font-weight:600;"><?= t('register_signin_link') ?></a>
        </p>
    </div>
<?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
<script>
(function () {
    const input  = document.getElementById('pswdTxt');
    if (!input) return;
    const bars   = [1,2,3,4].map(i => document.getElementById('bar-'+i));
    const label  = document.getElementById('pswd-label');
    const reqLen     = document.getElementById('req-len');
    const reqUpper   = document.getElementById('req-upper');
    const reqLower   = document.getElementById('req-lower');
    const reqNumber  = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    const levels = [
        { cls: 'active-weak',   text: 'Weak',   col: '#ff3b30' },
        { cls: 'active-fair',   text: 'Fair',   col: '#ff9500' },
        { cls: 'active-good',   text: 'Good',   col: '#34c759' },
        { cls: 'active-strong', text: 'Strong', col: 'var(--accent-blue)' },
    ];
    function score(v) {
        return [v.length >= 8, /[A-Z]/.test(v), /[a-z]/.test(v), /[0-9]/.test(v), /[^A-Za-z0-9]/.test(v)].filter(Boolean).length;
    }
    input.addEventListener('input', function () {
        const v = this.value;
        const r = { len: v.length >= 8, upper: /[A-Z]/.test(v), lower: /[a-z]/.test(v), number: /[0-9]/.test(v), special: /[^A-Za-z0-9]/.test(v) };
        const s = v.length === 0 ? 0 : Math.min(4, Math.max(1, Math.round(score(v) * 4 / 5)));
        reqLen.classList.toggle('met', r.len);
        reqUpper.classList.toggle('met', r.upper);
        reqLower.classList.toggle('met', r.lower);
        reqNumber.classList.toggle('met', r.number);
        reqSpecial.classList.toggle('met', r.special);
        bars.forEach((b, i) => { b.className = 'pswd-meter-bar'; if (v.length > 0 && i < s) b.classList.add(levels[s-1].cls); });
        label.textContent = v.length === 0 ? '' : levels[s-1].text;
        label.style.color = v.length === 0 ? '' : levels[s-1].col;
    });
})();
</script>
</body>
</html>
