<?php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['emailTxt'] ?? '');
    $password = trim($_POST['pswdTxt'] ?? '');

    $sql = "SELECT idUtente, pswd, isAdmin FROM utenti WHERE email = :e";
    $stm = $pdo->prepare($sql);
    $stm->bindParam(':e', $email);
    $stm->execute();

    $credentials = $stm->fetch(PDO::FETCH_ASSOC);

    if ($credentials && password_verify($password, $credentials['pswd'])) {
        session_start();
        $_SESSION["email"] = $email;
        $_SESSION["id"] = $credentials['idUtente'];
        $_SESSION["accept"] = "ACCEPT";
        $_SESSION["admin"] = $credentials['isAdmin'];
        header("Location: showeventstest.php");
        exit;
    } else {
        $error = "Email o password errate.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Tech Events</title>
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body>
    <nav class="glass-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo">Tech<span>Events</span></a>
            <div class="links">
                <a href="/">Overview</a>
                <a href="/sign_in.php" class="btn-primary-outline">Register</a>
            </div>
        </div>
    </nav>

    <form method="POST">
        <p class="section-label" style="text-align: left; margin-bottom: 8px;">Portal Access</p>
        <h1 style="font-family: var(--font-display); font-size: 28px; margin-bottom: 32px; font-weight: 800;">Sign in to Tech Events</h1>

        <p>Email Address</p>
        <input type="text" name="emailTxt" placeholder="name@organization.com" required>

        <p>Security Key</p>
        <input type="password" name="pswdTxt" placeholder="••••••••" required>

        <br>
        <button type="submit">Authorize Session</button>
        <br><br>
        
        <?php if (isset($error)): ?>
            <p style="color: #ff3b30; font-weight: 600; text-align: center;"><?php echo $error; ?></p>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 24px;">New organization? <a href="sign_in.php" style="font-weight: 700;">Create account</a></p>
    </form>
</body>
</html>