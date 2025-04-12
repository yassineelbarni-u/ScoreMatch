<?php
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tournoi_id = $_POST['tournoi_id'];
    $stmt = $pdo->prepare("DELETE FROM tournois WHERE id = ?");
    $stmt->execute([$tournoi_id]);
    echo json_encode(["status" => "success"]);
}
?>
