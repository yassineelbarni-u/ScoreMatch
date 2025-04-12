<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment']) && isset($_POST['match_id']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $match_id = intval($_POST['match_id']);
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO commentaires (user_id, match_id, contenu) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $match_id, $comment]);

        echo json_encode(["success" => true]);
        exit();
    }
}

echo json_encode(["success" => false, "message" => "Données invalides."]);
exit();
