<?php 
    require '../config.php';
    \App\Auth::requireAdmin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Evento — Tech Events</title>
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body>
    <form method="POST">
        <p>Nome Evento:</p>
        <input type="text" name="nameTxt" required>
        
        <p>Numero Posti:</p>
        <input type="number" name="sitsTxt" required>
        
        <p>Città:</p>
        <input type="text" name="cityTxt" required>
    
        <p>Paese:</p>
        <input type="text" name="regionTxt" required>
        
        <p>Data Inizio:</p>
        <input type="date" name="dateSTxt" required>

        <p>Data Fine:</p>
        <input type="date" name="dateETxt" required>

        <br><br>
        <button type="submit">Crea</button>
    </form>

    <?php 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['nameTxt']);
            $nSits = trim($_POST['sitsTxt']);
            $city = trim($_POST['cityTxt']);
            $region = trim($_POST['regionTxt']);
            $dateS = trim($_POST['dateSTxt']);
            $dateE = trim($_POST['dateETxt']);
            
            try {
                $pdo->beginTransaction();
                $sql = 'INSERT INTO evento (nome, nPosti, citta, paese, dataInizio, dataFine) VALUES (:n, :s, :c, :r, :ds, :de)';
                $stm = $pdo->prepare($sql);
    
                $stm->bindParam(':n', $name);
                $stm->bindParam(':s', $nSits);
                $stm->bindParam(':c', $city);
                $stm->bindParam(':r', $region);
                $stm->bindParam(':ds', $dateS);
                $stm->bindParam(':de', $dateE);
    
                $stm->execute();
                $pdo->commit();
                header("location: dashboard.php");
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo 'Errore: ' . $e->getMessage();
            }
        }
    ?>
</body>
</html>
