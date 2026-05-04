<?php
    require 'config.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="stylePhp.css">
    
</head>
<body>
    <form method="POST">
    
        <p>nome Gioco:</p>
        <input type="text" name="nameTxt" required>
        
        <p>copyright:</p>
        <input type="text" name="rightsTxt" required>

        <br>
        <button>crea</button>
    </form>
    <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                $name = trim($_POST['nameTxt']);
                $copyright = trim($_POST['rightsTxt']);

                
                try{
                    $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                    $pdo -> exec("BEGIN WORK");
                    $pdo -> exec("LOCK TABLES giochi WRITE");
        
                    $sql = 'INSERT INTO giochi VALUES (null, :n, :s)';
        
                    $stm = $pdo ->prepare($sql);
        
                    $stm -> bindParam(':n', $name);
                    $stm -> bindParam(':s', $copyright);
        
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

