<?php 
    require '../config.php';

    try {

        $sql = 'SELECT idSponsor, nomeAzienda FROM sponsor';
        $stm = $pdo ->prepare($sql);
        $stm->execute();
        $sponsors = $stm->fetchAll();
    } catch (PDOException $e) {
        echo $e;
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
    
        <p>nome:</p>
        <input type="text" name="nameTxt" required>
        
        <p>numero componenti:</p>
        <input type="number" name="membersTxt" required>
        
        <select name="sponsor">
            <?php foreach($sponsor as $spo): ?>
                <option value="<?= $spo["idSponsor"] ?>"> <?= $spo['nomeAzienda']?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <br>
        <button>crea</button>
    </form>

    <?php 
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['nameTxt'];
            $numberC = $_POST['membersTxt'];
            $idS = $_POST['sponsor'];

            try{
                $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                $pdo -> exec("BEGIN WORK");
                $pdo -> exec("LOCK TABLES squadre WRITE");
    
                $sql = 'INSERT INTO squadre VALUES (null, :n, :num, :idS)';
    
                $stm = $pdo ->prepare($sql);
    
                $stm -> bindParam(':n', $name);
                $stm -> bindParam(':num', $numberC);
                $stm -> bindParam(':idS', $idS);
    
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