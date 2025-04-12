<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json'); // Ajouter l'en-tête JSON

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type'];
$id = $_POST['id'];

try {
    if ($type == 'joueur') {
        $stmt = $pdo->prepare("SELECT * FROM abonnements WHERE user_id = ? AND joueur_id = ?");
        $stmt->execute([$user_id, $id]);
        
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO abonnements (user_id, joueur_id, date_abonnement) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $id]);
            echo json_encode(['status' => 'success', 'message' => '✅ Abonnement au joueur réussi !']);
        } else {
            echo json_encode(['status' => 'warning', 'message' => '⚠️ Vous êtes déjà abonné à ce joueur']);
        }
        
    } elseif ($type == 'equipe') {
        $stmt = $pdo->prepare("SELECT * FROM abonnements WHERE user_id = ? AND equipe_id = ?");
        $stmt->execute([$user_id, $id]);
        
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO abonnements (user_id, equipe_id, date_abonnement) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $id]);
            echo json_encode(['status' => 'success', 'message' => '✅ Abonnement à l\'équipe réussi !']);
        } else {
            echo json_encode(['status' => 'warning', 'message' => '⚠️ Vous êtes déjà abonné à cette équipe']);
        }
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Type d\'abonnement invalide']);
    }
} catch (PDOException $e) {
    error_log("Erreur SQL: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Erreur technique. Veuillez réessayer.']);
}

exit();