<?php
    require 'config.php';
    $idM = $_GET['id'];

    try{
        $sql = 'SELECT * FROM ruoli';

        $stm = $pdo ->prepare($sql);

        $stm -> execute();

        $roles = $stm -> fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e){
        echo $e -> getMessage();
    }
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

        <select name="role">
            <?php foreach($roles as $role): ?>
                <option value="<?= $role["idRuolo"] ?>"> <?= $role['nomeRuolo']?></option>
            <?php endforeach; ?>
        </select>

        <br>
        <button>crea</button>
    </form>

    <?php 
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $role = $_POST['role'];

            try{
                $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                $pdo -> exec("BEGIN WORK");
                $pdo -> exec("LOCK TABLES membri_ruoli WRITE");
    
                $sql = 'INSERT INTO membri_ruoli VALUES (:idR, :idM)';
    
                $stm = $pdo ->prepare($sql);
    
                $stm -> bindParam(':idR', $role);
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