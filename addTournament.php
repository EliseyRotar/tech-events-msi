<?php 
    require 'config.php';

    $sql = 'SELECT * FROM giochi';  
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $gamesInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
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
        <input type="number" name="sitsTxt" required>
                
        <p>giorno svolgimento:</p>
        <input type="date" name="dateSTxt" required>

        <select name="gioco">
            <?php foreach($gamesInfo as $game): ?>
                <option value="<?= $game["idGioco"] ?>"> <?= $game['nomeGioco']?></option>
            <?php endforeach; ?>
        </select>

        <br>
        <button>crea</button>
    </form>

    <?php 
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            
            try{
                $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                $pdo -> exec("BEGIN WORK");
                $pdo -> exec("LOCK TABLES evento WRITE");
    
                $sql = 'INSERT INTO tornei VALUES (null, )';
    
                $stm = $pdo ->prepare($sql);
    
                $stm -> bindParam(':', $);
                $stm -> bindParam(':', $);
                $stm -> bindParam(':', $);
                $stm -> bindParam(':', $);
                $stm -> bindParam(':', $);
                $stm -> bindParam(':', $);
    
                $stm -> execute();
    
                $pdo -> exec('COMMIT WORK');

                header("location: showeventstest.php");
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