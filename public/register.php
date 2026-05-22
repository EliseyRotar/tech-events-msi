<?php
require_once '../config.php';
require_once '../src/helpers.php';

// Lang switch handler (mirrors templates/layout/header.php)
if (isset($_GET['lang'])) {
    $supported = ['it', 'en'];
    $lang = in_array($_GET['lang'], $supported, true) ? $_GET['lang'] : 'it';
    setcookie('lang', $lang, time() + 365 * 24 * 3600, '/');
    header('Location: /register.php');
    exit;
}

$pageTitle = t('register_title') . ' — Tech Dragons Events';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = trim($_POST['emailTxt'] ?? '');
    $name    = trim($_POST['nameTxt'] ?? '');
    $surname = trim($_POST['surnameTxt'] ?? '');
    $codFisc = trim($_POST['fiscTxt'] ?? '');
    $date    = trim($_POST['dateTxt'] ?? '');
    $pswd    = $_POST['pswdTxt'] ?? '';

    // Validate required fields
    if ($name === '' || $surname === '' || $email === '' || $codFisc === '' || $date === '' || $pswd === '') {
        $errors[] = t('err_missing_fields');
    }

    // Validate email
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('err_invalid_email');
    }

    // Validate date
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

    // Validate password length
    if ($pswd !== '' && strlen($pswd) < 8) {
        $errors[] = t('err_pswd_length');
    }

    // Check duplicate email
    if (empty($errors) && $email !== '') {
        $check = $pdo->prepare("SELECT idUtente FROM utenti WHERE email = :e");
        $check->bindParam(':e', $email);
        $check->execute();
        if ($check->fetch()) {
            $errors[] = t('err_email_taken');
        }
    }

    // Insert
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $hash = password_hash($pswd, PASSWORD_ARGON2ID);
            $stm  = $pdo->prepare(
                "INSERT INTO utenti (codice_fiscale, nome, cognome, dataNascita, isAdmin, email, pswd)
                 VALUES (:c, :n, :s, :d, 0, :e, :h)"
            );
            $stm->bindParam(':c', $codFisc);
            $stm->bindParam(':n', $name);
            $stm->bindParam(':s', $surname);
            $stm->bindParam(':d', $date);
            $stm->bindParam(':e', $email);
            $stm->bindParam(':h', $hash);
            $stm->execute();
            $pdo->commit();

            // Welcome email via Resend
            require_once __DIR__ . '/../src/Mailer.php';
            \App\Mailer::send(
                $email,
                'Welcome to Tech Dragons Events',
                "Hi {$name},\n\nYour account has been created successfully.\n\nEmail: {$email}\n\nSign in at: http://{$_SERVER['HTTP_HOST']}/login.php\n\n— Tech Dragons Events"
            );

            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
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
                <label class="form-label" for="fiscTxt"><?= t('register_fiscal') ?></label>
                <input class="form-input" type="text" id="fiscTxt" name="fiscTxt"
                       value="<?= htmlspecialchars($_POST['fiscTxt'] ?? '', ENT_QUOTES) ?>"
                       placeholder="AAABBB00C11D222E" required>
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
                    <!-- Password strength meter -->
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
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
<script>
(function () {
    const input  = document.getElementById('pswdTxt');
    const bars   = [
        document.getElementById('bar-1'),
        document.getElementById('bar-2'),
        document.getElementById('bar-3'),
        document.getElementById('bar-4'),
    ];
    const label  = document.getElementById('pswd-label');
    const reqLen     = document.getElementById('req-len');
    const reqUpper   = document.getElementById('req-upper');
    const reqLower   = document.getElementById('req-lower');
    const reqNumber  = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    const levels = [
        { cls: 'active-weak',   text: 'Weak' },
        { cls: 'active-fair',   text: 'Fair' },
        { cls: 'active-good',   text: 'Good' },
        { cls: 'active-strong', text: 'Strong' },
    ];

    function check(v) {
        return {
            len:     v.length >= 8,
            upper:   /[A-Z]/.test(v),
            lower:   /[a-z]/.test(v),
            number:  /[0-9]/.test(v),
            special: /[^A-Za-z0-9]/.test(v),
        };
    }

    function score(r) {
        return [r.len, r.upper, r.lower, r.number, r.special]
            .filter(Boolean).length;
    }

    input.addEventListener('input', function () {
        const v = this.value;
        const r = check(v);
        const s = v.length === 0 ? 0 : Math.min(4, Math.max(1, Math.floor(score(r) * 4 / 5) + (r.len ? 0 : -1) + 1));

        // Update requirements
        reqLen.classList.toggle('met', r.len);
        reqUpper.classList.toggle('met', r.upper);
        reqLower.classList.toggle('met', r.lower);
        reqNumber.classList.toggle('met', r.number);
        reqSpecial.classList.toggle('met', r.special);

        // Update bars
        bars.forEach((b, i) => {
            b.className = 'pswd-meter-bar';
            if (v.length > 0 && i < s) {
                b.classList.add(levels[s - 1].cls);
            }
        });

        label.textContent = v.length === 0 ? '' : levels[s - 1].text;
        label.style.color = v.length === 0 ? '' : ['#ff3b30', '#ff9500', '#34c759', 'var(--accent-blue)'][s - 1];
    });
})();
</script>
</body>
</html>
