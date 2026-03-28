<?php
    /*
        todo: crea "aggiungi gioco"
        todo: mettere in relazione con le altre pagine (storici e home)
    */
    require 'config.php';
    session_start();
    
    $eventID = isset($_GET['id']) ? $_GET['id']:"";
    $tournamentInfo = null;
    
    $sql = 'SELECT * FROM evento';

    $stm = $pdo->prepare($sql);
    
    $stm->execute();

    $generalInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
    if ($eventID != null) {
        $sql = 'SELECT * FROM tornei WHERE tornei.idEvento = :id';
    $stm = $pdo->prepare($sql);
           
    $stm -> bindParam(':id', $eventID);
    $stm ->execute();

    $tournamentInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
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

    <a href="sign_in.php">sign in</a>
    <a href="login.php">log in</a>
    <a href="logout.php">log out</a>

    <table border>
        <tr>
            <th>eventi</th>
            <th>inizio evento</th>
            <th>fine evento</th>
            <th>citta</th>
            <th>paese</th>
            <th>posti disponibili</th>
            <th>azioni</th>
        </tr>
        <?php foreach ($generalInfo as $row): ?>
            <tr>
                <td><?= $row['nome'] ?></td>
                <td><?= $row['dataInizio'] ?></td>
                <td><?= $row['dataFine'] ?></td>
                <td><?= $row['citta'] ?></td>
                <td><?= $row['paese'] ?></td>
                <td><?= $row['nPosti'] ?></td>
                <td>   
                    <p>
                        <a href="showeventstest.php?id=<?= $row["idEvento"]?>"> info torneo </a>
                        <?php if(isset($_SESSION['admin']) && $_SESSION['admin'] == '1'): ?>
                            /   
                            <a href="addTournament.php?id=<?= $row["idEvento"]?>">aggiungi torneo</a>
                        <?php endif; ?>
                    </p> 

                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <?php if ($tournamentInfo != null): ?>
        <table border>
            <tr>
                <th>Squadra</th>
                <th>monte premi</th>
                <th>giorno</th>
                <th>Azioni</th>
            </tr>
            <?php foreach($tournamentInfo as $row): ?>
                <tr>
                    <td><?= $row['nomeTorneo']?></td>
                    <td><?= $row['montePremi']?></td>
                    <td><?= $row['giornoSvolgimento']?></td>
                    <td>
                        <p>
                            <a href="showTeam.php?id=">visualizza team </a>
                            /
                            <a href="addTeam.php?id=<?= $row["idEvento"]?>">aggiungi Team </a> <!--todo: crea la funzione -->
                        </p>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <br>
    <?php if(isset($_SESSION['admin']) && $_SESSION['admin'] == true): ?>
    <button><a href="createEvent.php">crea evento</a></button>
    <?php endif ?>

</body>
</html>
