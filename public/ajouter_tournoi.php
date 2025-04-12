<?php
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $admin_tournoi_id = $_POST['admin_tournoi_id'];

    if (!empty($nom) && !empty($date_debut) && !empty($date_fin) && !empty($admin_tournoi_id)) {
        $stmt = $pdo->prepare("INSERT INTO tournois (nom, date_debut, date_fin, admin_tournoi_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $date_debut, $date_fin, $admin_tournoi_id]);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Tous les champs sont requis"]);
    }
}
?>
