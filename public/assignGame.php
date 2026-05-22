<?php
    require '../config.php';
    $idM = $_GET['id'];

    try{
        $sql = 'SELECT * FROM giochi';

        $stm = $pdo ->prepare($sql);

        $stm -> execute();

        $games = $stm -> fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo''. $e -> getMessage() .'';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body>
    <form method="POST">
    
        <select name="game">
            <?php foreach($games as $game): ?>
                <option value="<?= $game["idGioco"] ?>"> <?= $game['nomeGioco']?></option>
            <?php endforeach; ?>
        </select>

        <br>
        <button>crea</button>
    </form>

    <?php 
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $game = $_POST['game'];

            try{
                $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                $pdo -> exec("BEGIN WORK");
                $pdo -> exec("LOCK TABLES giochi_membri WRITE");
    
                $sql = 'INSERT INTO giochi_membri VALUES (:idG, :idM)';
    
                $stm = $pdo ->prepare($sql);
    
                $stm -> bindParam(':idG', $game);
                $stm -> bindParam(':idM', $idM);

    
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