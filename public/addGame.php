<?php 
    require '../config.php';
    \App\Auth::requireAdmin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Gioco — Tech Events</title>
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body>
    <form method="POST">
        <p>Nome Gioco:</p>
        <input type="text" name="nameTxt" required>
        
        <p>Copyright:</p>
        <input type="text" name="copyTxt" required>
        
        <br><br>
        <button type="submit">Aggiungi</button>
    </form>

    <?php 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['nameTxt']);
            $copy = trim($_POST['copyTxt']);
            
            try {
                $pdo->beginTransaction();
                $sql = 'INSERT INTO giochi (nomeGioco, copyright) VALUES (:n, :c)';
                $stm = $pdo->prepare($sql);
                $stm->bindParam(':n', $name);
                $stm->bindParam(':c', $copy);
                $stm->execute();
                $pdo->commit();
                header("location: dashboard.php");
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo 'Errore: ' . $e->getMessage();
            }
        }
    ?>
</body>
</html>
