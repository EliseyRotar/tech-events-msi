<?php 
    require '../config.php';
    \App\Auth::requireAdmin();

    $sql = 'SELECT * FROM giochi';  
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $gamesInfo = $stm->fetchAll();

    $idE = $_GET['id'] ?? null;
    if (!$idE) {
        header("Location: showeventstest.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Torneo — Tech Events</title>
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
        <br>
        <br>
        <select name="gioco">
            <?php foreach($gamesInfo as $game): ?>
                <option value="<?= $game["idGioco"] ?>"> <?= $game['nomeGioco']?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <br>
        <button type="submit">Crea Torneo</button>
    </form>

    <?php 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['nameTxt']);
            $money = $_POST['moneyTxt'];
            $date = $_POST['dateSTxt'];
            $idG = $_POST['gioco'];

            try {
                $pdo->exec("SET SESSION idle_transaction_timeout = 5");
                $pdo->beginTransaction();
                
                $sql = 'INSERT INTO tornei (nomeTorneo, montePremi, giornoSvolgimento, idEvento, idGioco) VALUES (:n, :m, :d, :idE, :idG)';
                $stm = $pdo->prepare($sql);
    
                $stm->bindParam(':n', $name);
                $stm->bindParam(':m', $money);
                $stm->bindParam(':d', $date);
                $stm->bindParam(':idE', $idE);
                $stm->bindParam(':idG', $idG);
    
                if ($stm->execute()) {
                    $pdo->commit();
                    header("location: showeventstest.php");
                    exit;
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo 'Errore: ' . $e->getMessage();
            }
        }
    ?>
</body>
</html>
