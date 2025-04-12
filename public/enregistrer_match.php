<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['match_id'])) {
    $match_id = $_POST['match_id'];

    // Mettre à jour le statut du match en "Terminé"
    $stmt = $pdo->prepare("UPDATE matches SET statut = 'terminé' WHERE id = ?");
    $stmt->execute([$match_id]);

    echo json_encode(["status" => "success", "message" => "Le match a été enregistré et masqué."]);
    exit();
} else {
    echo json_encode(["status" => "error", "message" => "Erreur lors de l'enregistrement."]);
    exit();
}
?>
