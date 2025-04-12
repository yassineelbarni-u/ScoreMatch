<?php
require_once '../config/database.php';

$equipe1_id = $_GET['equipe1'] ?? null;
$equipe2_id = $_GET['equipe2'] ?? null;

if ($equipe1_id && $equipe2_id) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.nom 
        FROM stades s
        JOIN equipes e ON e.stade_id = s.id
        WHERE e.id IN (?, ?)
    ");
    $stmt->execute([$equipe1_id, $equipe2_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
