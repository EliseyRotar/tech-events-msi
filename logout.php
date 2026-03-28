<?php
session_start();

// svuota sessione
$_SESSION = [];

// distrugge sessione
session_destroy();

// reindirizza al login
header("Location: showeventstest.php");
exit;
?>