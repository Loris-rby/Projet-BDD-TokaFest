<?php
session_start();
session_destroy();
// Redirection vers le login dans le même dossier
header("Location: login.php");
exit;
?>