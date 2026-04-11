<?php
    require 'config.php';
    $idT = $_GET['id'];

    try {

        $sql = 'SELECT * FROM ruoli';

        $stm = $pdo->prepare($sql);

        $stm -> execute();

        $roles = $stm->fetchAll();

    } catch (PDOException $e) {
        echo $e->getMessage();
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
    
        <p>nome:</p>
        <input type="text" name="nameTxt" required>

        <p>cognome:</p>
        <input type="text" name="surnameTxt" required>

        <p>nickname:</p>
        <input type="text" name="nickTxt" required>

        <!-- <select name="role">
            <?php foreach($roles as $role): ?>
                <option value="<?= $role["idRuolo"] ?>"> <?= $role['nomeRuolo']?></option>
            <?php endforeach; ?>
        </select> -->

        <br>
        <button>crea</button>
    </form>

    <?php 
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name= trim($_POST['nameTxt']);
            $surname = trim($_POST['surnameTxt']);
            $test = null;
            try{

                $sql = 'SELECT * FROM utenti WHERE nome = :n AND cognome = :s';

                $stm = $pdo->prepare($sql); 

                $stm -> bindParam(':n', $name);
                $stm -> bindParam(':s', $surname);
               
                $stm -> execute();

                $users = $stm->fetchAll();

            } catch (PDOException $e) {
                echo $e->getMessage();
                echo "l'utente deve prima essere registrato";
            }

            foreach($users as $user){
                $test = (int)$user['idUtente'];
            }

            $nickname = trim($_POST['nickTxt']);
            // $role = trim($_POST['role']);

            try{
                $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                $pdo -> exec("BEGIN WORK");
                $pdo -> exec("LOCK TABLES membri WRITE");

    
                $sql = 'INSERT INTO membri VALUES (null, :n, :idT, :idU)';
    
                $stm = $pdo ->prepare($sql);
    
                $stm -> bindParam(':n', $nickname);
                $stm -> bindParam(':idT', $idT);
                $stm -> bindParam(':idU', $test);

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