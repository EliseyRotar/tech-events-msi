<?php
require_once '../config.php';

$pageTitle = "Organization Registration — Tech Events";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['emailTxt'] ?? '');
    $name = trim($_POST['nameTxt'] ?? '');
    $codFisc = trim($_POST['fiscTxt'] ?? '');
    $surname = trim($_POST['surnameTxt'] ?? ''); 
    $date = trim($_POST['dateTxt'] ?? '');
    $pswd = trim($_POST['pswdTxt'] ?? '');

    // Check if email already exists
    $checkSql = "SELECT idUtente FROM utenti WHERE email = :e";
    $checkStm = $pdo->prepare($checkSql);
    $checkStm->bindParam(':e', $email);
    $checkStm->execute();
    
    if ($checkStm->fetch()) {
        $error = "This email is already registered.";
    } else {
        try {
            $pdo->beginTransaction();
            $hashpass = password_hash($pswd, PASSWORD_ARGON2ID);

            $sql = "INSERT INTO utenti (codice_fiscale, nome, cognome, dataNascita, isAdmin, email, pswd) 
                    VALUES (:c, :n, :s, :d, 0, :e, :h)";

            $stm = $pdo->prepare($sql);
            $stm->bindParam(':c', $codFisc);
            $stm->bindParam(':n', $name);
            $stm->bindParam(':s', $surname);
            $stm->bindParam(':d', $date);
            $stm->bindParam(':e', $email);
            $stm->bindParam(':h', $hashpass);
            
            if ($stm->execute()) {
                $pdo->commit();
                header('Location: login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/php-pages.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 100px 24px; }
        form { max-width: 500px; }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    </style>
</head>
<body>
    <nav class="glass-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo">Tech<span>Events</span></a>
            <div class="links">
                <a href="/login.php" class="btn-primary-outline">Sign In</a>
            </div>
        </div>
    </nav>

    <form method="POST">
        <p class="section-label" style="text-align: left; margin-bottom: 8px;">Organization Portal</p>
        <h1 style="font-family: var(--font-display); font-size: 28px; margin-bottom: 32px; font-weight: 800;">Create Account</h1>

        <?php if (isset($error)): ?>
            <p style="color: #ff3b30; font-weight: 600; text-align: center; margin-bottom: 24px;"><?= $error ?></p>
        <?php endif; ?>

        <div class="grid-inputs">
            <div>
                <p>First Name</p>
                <input type="text" name="nameTxt" placeholder="John" required>
            </div>
            <div>
                <p>Last Name</p>
                <input type="text" name="surnameTxt" placeholder="Doe" required>
            </div>
        </div>

        <p>Email Address</p>
        <input type="text" name="emailTxt" placeholder="john@organization.com" required>

        <p>Fiscal Code / ID</p>
        <input type="text" name="fiscTxt" placeholder="AAABBB00C11D222E" required>

        <div class="grid-inputs">
            <div>
                <p>Date of Birth</p>
                <input type="date" name="dateTxt" required>
            </div>
            <div>
                <p>Security Key (Password)</p>
                <input type="password" name="pswdTxt" placeholder="••••••••" required>
            </div>
        </div>

        <br>
        <button type="submit">Initialize Account</button>
        
        <p style="text-align: center; margin-top: 24px;">Already registered? <a href="login.php" style="font-weight: 700;">Sign in here</a></p>
    </form>
</body>
</html>
