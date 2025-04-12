<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? $_GET['receiver_id'] : null;

// Récupérer les messages entre les utilisateurs
if ($receiver_id) {
    $stmt = $pdo->prepare("SELECT m.*, u1.nom AS sender_name, u2.nom AS receiver_name 
                           FROM messages m
                           JOIN users u1 ON m.sender_id = u1.id
                           JOIN users u2 ON m.receiver_id = u2.id
                           WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                           ORDER BY m.created_at ASC");
    $stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as $message) {
        echo "<div class='message'>";
        echo "<p><strong>" . htmlspecialchars($message['sender_name']) . " :</strong> " . htmlspecialchars($message['message']) . "</p>";
        echo "<p><small>" . $message['created_at'] . "</small></p>";
        echo "</div>";
    }
}
?>
