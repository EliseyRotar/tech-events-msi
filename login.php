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
    <body>
    <form method="POST" >

    <p>email:</p>
    <input type="text" name="emailTxt" required>

    <p>password:</p>
    <input type="password" name="pswdTxt" required>

    <br>
    <br>
    <button>accedi</button>
    <br>
    <br>
    <p>non hai un account? <a href="sign_in.php">registrati qui</a> </p>

    </form>

    <?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){

        $email = trim($_POST['emailTxt']);
        $pswd = trim($_POST['pswdTxt']);

        
        $sql = "SELECT idUtente, pswd, isAdmin FROM utenti where email = :e";
        $stm = $pdo -> prepare($sql);
        $stm -> bindParam('e', $email);
        $stm -> execute();

        $credentials = $stm -> fetchAll();

        if ($credentials){
            foreach ($credentials as $elem){
                $idUser = $elem['idUtente'];
                $hashpass = $elem['userPassword'];
                $isAdmin = $elem['isAdmin'];
            }
        }else{
            echo "accesso non andato a buon fine";
        }

        if(password_verify($pswd, $hashpass)){
            session_start();
            $_SESSION["email"] = $email;
            $_SESSION["id"] = $idUser;
            $_SESSION["accept"] = "ACCEPT";
            $_SESSION["admin"] = $isAdmin;
            header("location: showEvents.php");
        }else{
            echo('male non ti sei loggato');
        }

    }


    ?>




</body>
</html>