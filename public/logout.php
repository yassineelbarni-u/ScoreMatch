<?php
session_start();
session_destroy(); // Détruit toutes les variables de session
header("Location: login.php"); // Redirige vers la page de connexion
exit();
?>
