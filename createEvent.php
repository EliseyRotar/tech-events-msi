<?php 
    require 'config.php';
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
        
        <p>numero posti:</p>
        <input type="number" name="sitsTxt" required>
        
        <p>città:</p>
        <input type="text" name="cityTxt" required>
    
        <p>paese:</p>
        <input type="text" name="regionTxt" required>
        
        <p>data inizio:</p>
        <input type="date" name="dateSTxt" required>

        <p>data fine:</p>
        <input type="date" name="dateETxt" required>

        <br>
        <button>crea</button>
    </form>

    <?php 
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['nameTxt']);
            $nSits = trim($_POST['sitsTxt']);
            $city= trim($_POST['cityTxt']);
            $region = trim($_POST['regionTxt']);
            $dateS = trim($_POST['dateSTxt']);
            $dateE = trim($_POST['dateETxt']);
            
            try{
                $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                $pdo -> exec("BEGIN WORK");
                $pdo -> exec("LOCK TABLES evento WRITE");
    
                $sql = 'INSERT INTO evento VALUES (null, :n, :s, :c, :r, :ds, :de)';
    
                $stm = $pdo ->prepare($sql);
    
                $stm -> bindParam(':n', $name);
                $stm -> bindParam(':s', $nSits);
                $stm -> bindParam(':c', $city);
                $stm -> bindParam(':r', $region);
                $stm -> bindParam(':ds', $dateS);
                $stm -> bindParam(':de', $dateE);
    
                $stm -> execute();
    
                $pdo -> exec('COMMIT WORK');

                header("location: showEvents.php");
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