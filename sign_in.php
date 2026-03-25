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
            $pswd = trim($_POST['pswdTxt']);


            if (!in_array($email, $emails) ){
                
                $hashpass = password_hash($pswd, PASSWORD_ARGON2ID);
    
                $sql = "INSERT INTO utenti values (null, :c, :n, :s, :d, :e, :h)";
    
                $stm = $pdo -> prepare($sql);
                $stm -> bindParam(':c', $codFisc);
                $stm -> bindParam(':n', $name);
                $stm -> bindParam(':s', $surname);
                $stm -> bindParam(':d', $date);
                $stm -> bindParam(':e', $email);
                $stm -> bindParam(':h', $hashpass);
                $stm -> execute();

                header('location: login.php');

            } else{
                echo "utente già registrato";
            }


        }
    ?>



</body>
</html>