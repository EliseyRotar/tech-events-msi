<?php
    require 'config.php';
    $idT = $_GET['id'];
    $idS = isset($_GET['Team']) ? $_GET['Team'] : "";
    $memberList = null;

    try {
    $sql = 'SELECT s.idSquadra, s.nomeSquadra, s.nComponenti, sp.nomeAzienda
            FROM tornei_squadre ts
            JOIN squadre s ON s.idSquadra = ts.idSquadra
            LEFT JOIN sponsor sp ON s.idSponsor = sp.idSponsor
            WHERE ts.idTorneo = :id;';
    $stm = $pdo->prepare($sql);
    $stm -> bindParam(':id', $idT);
    $stm -> execute();

    $teamsList = $stm -> fetchAll();
    } catch (PDOException $e) {
        echo $e;
    }
    if ($idS != '') {
        $sql = 'SELECT 
                    m.idMembro,
                    m.nickname, 
                    r.nomeRuolo, 
                    g.nomeGioco
                FROM membri m
                LEFT JOIN membri_ruoli mr 
                    ON m.idMembro = mr.idMembro
                LEFT JOIN ruoli r 
                    ON mr.idRuolo = r.idRuolo
                LEFT JOIN giochi_membri gm 
                    ON m.idMembro = gm.idMembro
                LEFT JOIN giochi g 
                    ON gm.idGioco = g.idGioco
                WHERE m.idSquadra = :id;';
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
                    <td>azioni</td>
                </tr>
                <?php foreach ($memberList as $row):?>
                    <tr>
                        <td><?= $row['nickname']?></td>
                        <td><?= $row['nomeRuolo']?></td>
                        <td><?= $row['nomeGioco']?></td>
                        <td>
                            <a href="assignRole.php?id=<?=$row['idMembro'] ?>">assegna ruolo</a>
                            /
                            <a href="assignGame.php?id=<?=$row['idMembro'] ?>">assegna gioco</a>
                        </td>
                    </tr>
                <?php endforeach;?>
            </table>
            <button><a href="addMember.php?id=<?=$idT?>">aggiungi membro</a></button>

        <?php endif;?>
</body>
</html>