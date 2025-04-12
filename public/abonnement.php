<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$match_id = $_POST['match_id'];

// Vérifier si l'utilisateur est déjà abonné
$stmt = $pdo->prepare("SELECT * FROM abonnements WHERE user_id = ? AND match_id = ?");
$stmt->execute([$user_id, $match_id]);

if ($stmt->rowCount() == 0) {
    // Ajouter l'abonnement
    $stmt = $pdo->prepare("INSERT INTO abonnements (user_id, match_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $match_id]);

    // Récupérer les noms des équipes pour la notification
    $stmt = $pdo->prepare("
        SELECT e1.nom AS equipe1, e2.nom AS equipe2, m.date_match
        FROM matches m
        JOIN equipes e1 ON m.equipe1_id = e1.id
        JOIN equipes e2 ON m.equipe2_id = e2.id
        WHERE m.id = ?
    ");
    $stmt->execute([$match_id]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($match) {
        $equipe1 = $match['equipe1'];
        $equipe2 = $match['equipe2'];
        $date_match = date('d/m/Y H:i', strtotime($match['date_match']));

        // Ajouter une notification avec les détails du match
        $message = "Vous êtes abonné au match **$equipe1 vs $equipe2** prévu le **$date_match**.";
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, match_id, message, vue, date_notification) VALUES (?, ?, ?, 0, NOW())");
        $stmt->execute([$user_id, $match_id, $message]);
    }

    echo json_encode(['status' => 'success', 'message' => '✅ Vous êtes maintenant abonné au match ' . $equipe1 . ' vs ' . $equipe2]);
} else {
    echo json_encode(['status' => 'warning', 'message' => '⚠️ Vous êtes déjà abonné à ce match.']);
}

exit();
