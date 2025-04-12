<?php
$host = "localhost";  // Serveur MySQL (généralement "localhost" pour XAMPP)
$dbname = "score_matches";  // Nom de ta base de données
$username = "root";  // Nom d'utilisateur MySQL (par défaut "root" sur XAMPP)
$password = "";  // Mot de passe vide par défaut sur XAMPP

try {
    // Création de la connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
