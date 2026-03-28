<?php 
    require 'config.php';

    $sql = 'SELECT * FROM giochi';  
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $gamesInfo = $stm->fetchAll();

    $idE = $_GET['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST">
    
        <p>nome:</p>
        <input type="text" name="nameTxt" required>
        
        <p>monte premi:</p>
        <input type="number" name="moneyTxt" required>
                
        <p>giorno svolgimento:</p>
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
        <button>crea</button>
    </form>

    <?php 
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['nameTxt'];
            $money= $_POST['moneyTxt'];
            $date = $_POST['dateSTxt'];
            $idG = $_POST['gioco'];

            try{
                $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                $pdo -> exec("BEGIN WORK");
                $pdo -> exec("LOCK TABLES evento WRITE");
                $pdo -> exec("LOCK TABLES tornei_squadre WRITE");
                $pdo -> exec("LOCK TABLES tornei WRITE");
    
                $sql = 'INSERT INTO tornei VALUES (null, :n, :m, :d, :idE, :idG)';
    
                $stm = $pdo ->prepare($sql);
    
                $stm -> bindParam(':n', $name);
                $stm -> bindParam(':m', $money);
                $stm -> bindParam(':d', $date);
                $stm -> bindParam(':idE', $idE);
                $stm -> bindParam(':idG', $idG);
    
                $result = $stm -> execute();
                if ($result === true) {
                    $pdo -> exec('COMMIT WORK');
                    header("location: showeventstest.php");
                }
            } catch (PDOException $e) {
                echo ''. $e -> getMessage() .'';
                $pdo -> exec('ROLLBACK WORK');
            } finally {
                $pdo -> exec('UNLOCK TABLES');
            }
        }


    ?>


</body>
</html>