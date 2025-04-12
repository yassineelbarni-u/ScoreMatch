<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$sender_id = $_SESSION['user_id'];

// Vérifier que les données du formulaire sont présentes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receiver_id'], $_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    // Insérer le message dans la base de données
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    if ($stmt->execute([$sender_id, $receiver_id, $message])) {
        echo "Message envoyé avec succès.";
    } else {
        echo "Erreur lors de l'envoi du message.";
    }
}
?>
