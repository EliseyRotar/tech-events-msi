<?php
    require 'config.php';
    session_start();
    
    $teamID = isset($_GET['id']) ? $_GET['id']:"";
    $TeamInfo = null;
    
    $sql = 'SELECT nome, dataInizio, dataFine, nomeGioco, nomeSquadra, nPosti, squadre.idSquadra FROM evento, tornei, giochi, tornei_squadre, squadre 
    WHERE evento.idEvento = tornei.idEvento
    AND tornei.idGioco = giochi.idGioco
    AND tornei.idTorneo = tornei_squadre.idTorneo
    AND tornei_squadre.idSquadra = squadre.idSquadra';

    $stm = $pdo->prepare($sql);
    
    $stm->execute();

    $generalInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
    if ($teamID != null) {
        $sql = 'SELECT nomeSquadra, nickname, nomeRuolo FROM squadre, membri, membri_ruoli, ruoli
        WHERE membri.idSquadra = squadre.idSquadra
        AND squadre.idSquadra = :d
        AND membri.idMembro = membri_ruoli.idMembro
        AND membri_ruoli.idRuolo = ruoli.idRuolo';
    $stm = $pdo->prepare($sql);
           
    $stm -> bindParam(':d', $teamID);
    $stm ->execute();

    $TeamInfo = $stm->fetchAll(PDO::FETCH_ASSOC);
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
            <th>eventi</th>
            <th>inizio evento</th>
            <th>fine evento</th>
            <th>giochi</th>
            <th>squadre</th>
            <th>posti disponibili</th>
            <th>azioni</th>
        </tr>
        <?php foreach ($generalInfo as $row): ?>
            <tr>
                <td><?= $row['nome'] ?></td>
                <td><?= $row['dataInizio'] ?></td>
                <td><?= $row['dataFine'] ?></td>
                <td><?= $row['nomeGioco'] ?></td>
                <td><?= $row['nomeSquadra'] ?></td>
                <td><?= $row['nPosti'] ?></td>
                <td>
                    <a href="showEvents.php?id=<?= $row['idSquadra'] ?>"> info team </a>
                    <!-- aggiungi visualizza tornei forse al posto di info team -->
                    <?php if($_SESSION['admin'] == true): ?>
                    <a href="">aggiungi torneo</a> <!-- fai sta funzione -->
                    <? endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <?php if ($TeamInfo != null): ?>
        <table border>
            <tr>
                <th>Squadra</th>
                <th>Giocatore</th>
                <th>Ruolo</th>
            </tr>
            <?php foreach($TeamInfo as $row): ?>
                <tr>
                    <td><?= $row['nomeSquadra']?></td>
                    <td><?= $row['nickname']?></td>
                    <td><?= $row['nomeRuolo']?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <br>
    <?php if($_SESSION['admin'] == true): ?>
    <button><a href="createEvent.php">crea evento</a></button>
    <?php endif ?>
</body>
</html>
