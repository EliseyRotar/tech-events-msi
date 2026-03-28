<?php
    require 'config.php';


    $sql = "SELECT email FROM utenti";
    $stm = $pdo -> prepare($sql);
    $stm -> execute();
    $emails = $stm -> fetch(PDO::FETCH_NUM);
    

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css"></a>
</head>

<body>
    <form method="POST">

    <p>email:</p>
    <input type="text" name="emailTxt" required>

    <p>codice Fiscale:</p>
    <input type="text" name="fiscTxt" required>

    <p>nome:</p>
    <input type="text" name="nameTxt" required>
  
    <p>cognome:</p>
    <input type="text" name="surnameTxt" required>

    <p>admin (1, 0):</p>
    <input type="number" name="adminTxt" required>
    
    <p>data:</p>
    <input type="date" name="dateTxt" required>

    <p>password:</p>
    <input type="password" name="pswdTxt" required>

    <br>
    <br>
    <button>registrati</button>
    </form>

    <?php

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){

            $email = trim($_POST['emailTxt']);
            $name = trim($_POST['nameTxt']);
            $codFisc = trim($_POST['fiscTxt']);
            $surname = trim($_POST['surnameTxt']); 
            $date = trim($_POST['dateTxt']);
            $admin = trim($_POST['adminTxt']);
            $pswd = trim($_POST['pswdTxt']);


            if (!in_array($email, $emails) ){
                
                try{
                $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
                $pdo -> exec("BEGIN WORK");
                $pdo -> exec("LOCK TABLES UTENTI WRITE");

                $hashpass = password_hash($pswd, PASSWORD_ARGON2ID);
    
                $sql = "INSERT INTO utenti values (null, :c, :n, :s, :d, :a,:e, :h)";
    
                $stm = $pdo -> prepare($sql);
                $stm -> bindParam(':c', $codFisc);
                $stm -> bindParam(':n', $name);
                $stm -> bindParam(':s', $surname);
                $stm -> bindParam(':d', $date);
                $stm -> bindParam(':a', $admin);
                $stm -> bindParam(':e', $email);
                $stm -> bindParam(':h', $hashpass);
                $result = $stm -> execute();

                if ($result === true){
                    $pdo -> query('COMMIT WORK');

                }

                header('location: login.php');
                }catch(PDOException $e){
                    echo $e;
                    $pdo -> exec('ROLLBACK WORK');
                } finally{
                    $pdo -> exec('UNLOCK TABLES');
                }

            } else{
                echo "utente già registrato";
            }


        }
    ?>



</body>
</html>