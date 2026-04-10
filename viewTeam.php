<?php
    require 'config.php';
    $idT = $_GET['id'];
    $idS = isset($_GET['Team']) ? $_GET['Team'] : "";

    try {

    $sql = 'SELECT squadre.idSquadra, nomeSquadra, nComponenti, nomeAzienda FROM squadre, sponsor, tornei_squadre WHERE squadre.idSquadra = tornei_squadre.idSquadra AND tornei_squadre.idTorneo = :id AND squadre.idSponsor = sponsor.idSponsor;';
    $stm = $pdo->prepare($sql);
    $stm -> bindParam(':id', $idT);
    $stm -> execute();

    $teamsList = $stm -> fetchAll();
    } catch (PDOException $e) {
        echo $e;
    }
    if ($idS != '') {
        $sql = 'SELECT nickname, nomeRuolo, nomeGioco FROM membri, ruoli, giochi, membri_ruoli, giochi_membri WHERE membri.idSquadra = :id AND membri.idMembro = membri_ruoli.idMembro AND membri_ruoli.idRuolo = ruoli.idRuolo AND membri.idMembro = giochi_membri.idMembro AND giochi_membri.idGioco = giochi.idGioco';
        $stm = $pdo->prepare($sql);
        $stm -> bindParam(':id', $idS);
        $stm -> execute();

        $memberList = $stm -> fetchAll();
        
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
    <table border>
        <tr>
            <th>nome squadra</th>
            <th>numero componenti</th>
            <th>sponsor</th>
            <th>azioni</th>
        </tr>
        <?php foreach($teamsList as $row):?>
            <tr>
                <td><?= $row['nomeSquadra']?></td>
                <td><?= $row['nComponenti']?></td>
                <td><?= $row['nomeAzienda']?></td>
                <td><a href="viewTeam.php?id=<?=$idT?>&Team=<?=$row['idSquadra'] ?>">visualizza membri</a></td>
            </tr>
        <?php endforeach;?>
    </table>
        <br>
        <?php if ($memberList):?>
            <table border>
                <tr>
                    <td>giocatore</td>
                    <td>ruolo</td>
                    <td>gioco</td>
                </tr>
                <?php foreach ($memberList as $row):?>
                    <tr>
                        <td><?= $row['nickname']?></td>
                        <td><?= $row['nomeRuolo']?></td>
                        <td><?= $row['nomeGioco']?></td>
                    </tr>
                <?php endforeach;?>
            </table>
        <?php endif;?>
</body>
</html>