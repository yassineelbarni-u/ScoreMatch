<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['match_id'], $_POST['equipe_votee_id']) && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $matchId = intval($_POST['match_id']);
    $equipeVoteeId = intval($_POST['equipe_votee_id']);

    // Vérifier si l'utilisateur a déjà voté pour ce match
    $stmt = $pdo->prepare("SELECT * FROM votes WHERE user_id = ? AND match_id = ?");
    $stmt->execute([$userId, $matchId]);

    if ($stmt->rowCount() == 0) {
        // Récupérer le nom de l'équipe votée
        $stmt = $pdo->prepare("SELECT nom FROM equipes WHERE id = ?");
        $stmt->execute([$equipeVoteeId]);
        $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($equipe) {
            $nomEquipeVotee = $equipe['nom'];

            // Enregistrer le vote
            $stmt = $pdo->prepare("INSERT INTO votes (user_id, match_id, equipe_votee_id) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $matchId, $equipeVoteeId]);

            echo json_encode([
                "success" => true,
                "message" => "✅ Vous avez voté pour l'équipe : <strong>{$nomEquipeVotee}</strong>."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "❌ Équipe introuvable."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "⚠️ Vous avez déjà voté pour ce match."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "❌ Requête invalide."
    ]);
}
?>
