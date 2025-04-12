<?php
require_once '../config/database.php';

if (!isset($_GET['equipe1']) || !isset($_GET['equipe2'])) {
    echo json_encode(["error" => "Équipes non spécifiées"]);
    exit();
}

$equipe1_id = intval($_GET['equipe1']);
$equipe2_id = intval($_GET['equipe2']);

// Récupérer les joueurs de l'équipe 1
$stmt1 = $pdo->prepare("SELECT id, nom, prenom FROM joueurs WHERE equipe_id = ?");
$stmt1->execute([$equipe1_id]);
$joueursEquipe1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les joueurs de l'équipe 2
$stmt2 = $pdo->prepare("SELECT id, nom, prenom FROM joueurs WHERE equipe_id = ?");
$stmt2->execute([$equipe2_id]);
$joueursEquipe2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["equipe1" => $joueursEquipe1, "equipe2" => $joueursEquipe2]);
?>
