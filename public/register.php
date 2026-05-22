<?php
require_once '../config.php';

$pageTitle = 'Create Account — Tech Dragons Events';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['emailTxt'] ?? '');
    $name     = trim($_POST['nameTxt'] ?? '');
    $surname  = trim($_POST['surnameTxt'] ?? '');
    $codFisc  = trim($_POST['fiscTxt'] ?? '');
    $date     = trim($_POST['dateTxt'] ?? '');
    $pswd     = trim($_POST['pswdTxt'] ?? '');

    $check = $pdo->prepare("SELECT idUtente FROM utenti WHERE email = :e");
    $check->bindParam(':e', $email);
    $check->execute();

    if ($check->fetch()) {
        $error = 'This email address is already registered.';
    } else {
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
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
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
            <a href="/">Overview</a>
            <a href="/login.php" class="btn-secondary" style="padding:8px 18px;">Sign In</a>
        </div>
    </div>
</nav>

<div class="form-page">
    <div class="form-card form-card-wide">
        <span class="section-label">Organisation Portal</span>
        <h1>Create Account</h1>
        <p class="lead">Join the platform powering professional esports worldwide.</p>

        <?php if (isset($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="nameTxt">First Name</label>
                    <input class="form-input" type="text" id="nameTxt" name="nameTxt"
                           placeholder="John" required autocomplete="given-name">
                </div>
                <div class="form-group">
                    <label class="form-label" for="surnameTxt">Last Name</label>
                    <input class="form-input" type="text" id="surnameTxt" name="surnameTxt"
                           placeholder="Doe" required autocomplete="family-name">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="emailTxt">Email Address</label>
                <input class="form-input" type="email" id="emailTxt" name="emailTxt"
                       placeholder="john@organization.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="fiscTxt">Fiscal Code / Government ID</label>
                <input class="form-input" type="text" id="fiscTxt" name="fiscTxt"
                       placeholder="AAABBB00C11D222E" required>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="dateTxt">Date of Birth</label>
                    <input class="form-input" type="date" id="dateTxt" name="dateTxt"
                           required autocomplete="bday">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pswdTxt">Password</label>
                    <input class="form-input" type="password" id="pswdTxt" name="pswdTxt"
                           placeholder="••••••••" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn-primary btn-submit">Initialize Account</button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:14px;color:var(--text-secondary);">
            Already have an account?
            <a href="/login.php" style="color:var(--accent-blue);font-weight:600;">Sign in</a>
        </p>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
