<?php
    require 'config.php';
    $idT = $_GET['id'];
    $team = isset($_GET['Team']) ? $_GET['Team']:"";
    try {
        $sql = 'SELECT * FROM squadre';
        $stm = $pdo->prepare($sql);
        $stm -> execute();

        $teamsList = $stm -> fetchAll();
    } catch (PDOException $e) {
        echo $e;
    }

    if ($team){
      try{
            $pdo -> exec("SET SESSION idle_transaction_timeout = 5");
            $pdo -> exec("BEGIN WORK");
            $pdo -> exec("LOCK TABLES tornei_squadre WRITE");
            
            $sql = 'INSERT INTO tornei_squadre VALUES (:idT, :team)';
    
            $stm = $pdo ->prepare($sql);
    
            $stm -> bindParam(':idT', $idT);
            $stm -> bindParam(':team', $team);
    
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="stylePhp.css">
</head>
<body>
    <table>
        <tr>
            <th>nome squadra</th>
            <th>numero componenti</th>
            <th>azioni</th>
            <th>test</th>
        </tr>
        <?php foreach($teamsList as $row):?>
            <tr>
                <td><?= $row['nomeSquadra']?></td>
                <td><?= $row['nComponenti']?></td>
                <td><a href="signTeam.php?id=<?=$idT?>&Team=<?=$row['idSquadra'] ?>">iscrivi</a></td>
                <td><?= $idT?></td>
            </tr>
        <?php endforeach;?>
    </table>
</body>
</html>