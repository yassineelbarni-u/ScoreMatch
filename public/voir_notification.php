<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: notifications.php");
    exit();
}

$notif_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Mettre à jour la notification pour qu'elle soit "vue"
    $stmt = $pdo->prepare("UPDATE notifications SET vue = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);

    // Redirection vers la page des notifications
    header("Location: notifications.php");
    exit();
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
