<?php
require '../config.php';
require_once __DIR__ . '/../src/helpers.php';
\App\Auth::requireAdmin();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['nameTxt'] ?? '');
    $copy = trim($_POST['copyTxt'] ?? '');
    $error = runInTransaction($pdo, function() use ($pdo, $name, $copy) {
        $sql = 'INSERT INTO giochi (nomeGioco, copyright) VALUES (:n, :c)';
        $stm = $pdo->prepare($sql);
        $stm->bindParam(':n', $name);
        $stm->bindParam(':c', $copy);
        $stm->execute();
    }, 'dashboard.php');
    // on success runInTransaction redirects; only reaches here on error
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Gioco — Tech Dragons Events</title>
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body>
    <form method="POST">
        <p>Nome Gioco:</p>
        <input type="text" name="nameTxt" required>

        <p>Copyright:</p>
        <input type="text" name="copyTxt" required>

        <?php if ($error): ?>
            <p style="color: #ff3b30; margin-top: 12px;">Errore: <?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <br><br>
        <button type="submit">Aggiungi</button>
    </form>
</body>
</html>
