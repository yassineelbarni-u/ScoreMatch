<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $match_id = $_POST['match_id'];
    $joueur_id = $_POST['joueur_id'] ?? NULL;
    $minute = $_POST['minute'] ?? NULL;
    $type_event = $_POST['type_event'] ?? NULL;
    $carton = $_POST['carton'] ?? NULL;
    $minute_carton = $_POST['minute_carton'] ?? NULL;

    if (!$joueur_id) {
        echo json_encode(["status" => "error", "message" => "Veuillez sélectionner un joueur"]);
        exit();
    }

    // Récupérer l'équipe du joueur
    $stmt = $pdo->prepare("SELECT equipe_id FROM joueurs WHERE id = ?");
    $stmt->execute([$joueur_id]);
    $equipe_id = $stmt->fetchColumn() ?: NULL;

    if ($joueur_id && $minute && $equipe_id && $type_event) {
        $stmt = $pdo->prepare("INSERT INTO match_events (match_id, joueur_id, equipe_id, type_event, minute_but, carton, minute_carton) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$match_id, $joueur_id, $equipe_id, $type_event, $minute, $carton, $minute_carton]);

        echo json_encode(["status" => "success", "message" => "Événement ajouté avec succès"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'ajout de l'événement"]);
        exit();
    }
}
?>
