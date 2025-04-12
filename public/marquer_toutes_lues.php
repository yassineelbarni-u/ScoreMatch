<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: notifications.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Marquer toutes les notifications comme lues
    $stmt = $pdo->prepare("UPDATE notifications SET vue = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Redirection vers la page des notifications
    header("Location: notifications.php");
    exit();
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
