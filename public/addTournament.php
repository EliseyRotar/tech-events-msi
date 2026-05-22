<?php
require '../config.php';
\App\Auth::requireAdmin();

$idE = $_GET['id'] ?? null;
if (!$idE) {
    header("Location: dashboard.php");
    exit;
}

$sql = 'SELECT idGioco, nomeGioco FROM giochi';
$stm = $pdo->prepare($sql);
$stm->execute();
$gamesInfo = $stm->fetchAll(PDO::FETCH_ASSOC);

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['nameTxt'] ?? '');
    $money = trim($_POST['moneyTxt'] ?? '');
    $date  = trim($_POST['dateSTxt'] ?? '');
    $idG   = (int)($_POST['gioco'] ?? 0);

    try {
        $pdo->beginTransaction();
        $sql = 'INSERT INTO tornei (nomeTorneo, montePremi, giornoSvolgimento, idEvento, idGioco) VALUES (:n, :m, :d, :idE, :idG)';
        $stm = $pdo->prepare($sql);
        $stm->bindParam(':n', $name);
        $stm->bindParam(':m', $money);
        $stm->bindParam(':d', $date);
        $stm->bindParam(':idE', $idE);
        $stm->bindParam(':idG', $idG);
        $stm->execute();
        $pdo->commit();
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Torneo — Tech Dragons Events</title>
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body>
    <form method="POST">
        <p>Nome Torneo:</p>
        <input type="text" name="nameTxt" required>

        <p>Montepremi:</p>
        <input type="number" name="moneyTxt" required>

        <p>Giorno Svolgimento:</p>
        <input type="date" name="dateSTxt" required>
        <br><br>

        <select name="gioco">
            <?php foreach ($gamesInfo as $game): ?>
                <option value="<?= htmlspecialchars($game['idGioco'], ENT_QUOTES) ?>">
                    <?= htmlspecialchars($game['nomeGioco']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($error): ?>
            <p style="color: #ff3b30; margin-top: 12px;">Errore: <?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <br><br>
        <button type="submit">Crea Torneo</button>
    </form>
</body>
</html>
