<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['html' => "<li class='dropdown-item'>⚠️ Connectez-vous pour voir vos notifications.</li>", 'count' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer uniquement les notifications liées aux abonnements récents
$stmt = $pdo->prepare("
    SELECT n.id, n.message, n.date_notification, n.match_id, m.equipe1_id, m.equipe2_id, e1.nom AS equipe1, e2.nom AS equipe2
    FROM notifications n
    JOIN matches m ON n.match_id = m.id
    JOIN equipes e1 ON m.equipe1_id = e1.id
    JOIN equipes e2 ON m.equipe2_id = e2.id
    WHERE n.user_id = ? 
    ORDER BY n.date_notification DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le nombre total de notifications non lues
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND vue = 0");
$stmt->execute([$user_id]);
$notif_count = $stmt->fetchColumn();

$html = "";
if (empty($notifications)) {
    $html .= "<li class='dropdown-item'>📭 Aucune nouvelle notification.</li>";
} else {
    foreach ($notifications as $notif) {
        $html .= "<li class='dropdown-item'>";
        $html .= "<a href='match_details.php?match_id=" . $notif['match_id'] . "' class='text-decoration-none'>";
        $html .= "📢 " . htmlspecialchars($notif['equipe1']) . " vs " . htmlspecialchars($notif['equipe2']);
        $html .= " - " . date('d/m/Y H:i', strtotime($notif['date_notification']));
        $html .= "</a></li>";
    }
}

echo json_encode(['html' => $html, 'count' => $notif_count]);
exit();
