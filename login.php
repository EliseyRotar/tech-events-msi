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
        $passwd = trim($_POST['pswdTxt']);

        
        $sql = "SELECT idUtente, pswd, isAdmin FROM utenti where email = :e";
        $stm = $pdo -> prepare($sql);
        $stm -> bindParam('e', $email);
        $stm -> execute();
        
        $credentials = $stm -> fetch();
        
        if($credentials && password_verify($passwd, $credentials['pswd'])){
            session_start();
            $_SESSION["email"] = $email;
            $_SESSION["id"] = $credentials['idUser'];
            $_SESSION["accept"] = "ACCEPT";
            $_SESSION["admin"] = $credentials['isAdmin'];
            header("location: showeventstest.php");
        }else{
            echo('e-mail o password errate');
        }

    }
    ?>




</body>
</html>