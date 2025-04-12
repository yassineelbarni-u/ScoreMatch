<?php
require_once '../config/database.php';

// Générer un mot de passe sécurisé avec password_hash()
$password = password_hash('motdepasse123', PASSWORD_DEFAULT);

$query = "INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($query);
$stmt->execute(['Admin Global', 'admin@gestionmatchs.com', $password, 'admin_global']);

echo "Admin Global ajouté avec succès !";
?>
