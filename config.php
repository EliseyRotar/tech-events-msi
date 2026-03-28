<?php
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=innovation",'root','');
        $pdo -> exec('SET autocommit = 0');
    } catch (PDOException $e) {
        echo 'errore'. $e->getMessage();
    }
?>