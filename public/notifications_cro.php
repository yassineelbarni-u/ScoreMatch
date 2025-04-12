<?php
require_once '../config/database.php';

try {
    // Sélectionner les abonnements dont le match commence dans moins de 30 minutes et pas encore envoyée
    $sql = "
        SELECT ab.user_id, ab.match_id, m.equipe1_id, m.equipe2_id, m.date_match, m.statut
        FROM abonnements ab
        JOIN matches m ON ab.match_id = m.id
        WHERE TIMESTAMPDIFF(MINUTE, NOW(), m.date_match) >= 0
        AND ab.notification_envoyee = 0
    ";
    $stmt = $pdo->query($sql);
    $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($abonnements as $abonnement) {
        $user_id = $abonnement['user_id'];
        $match_id = $abonnement['match_id'];
        $equipe1_id = $abonnement['equipe1_id'];
        $equipe2_id = $abonnement['equipe2_id'];
        $date_match = $abonnement['date_match'];

        // Récupérer les noms des équipes
        $stmt = $pdo->prepare("SELECT nom FROM equipes WHERE id = ?");
        $stmt->execute([$equipe1_id]);
        $equipe1_nom = $stmt->fetchColumn();

        $stmt->execute([$equipe2_id]);
        $equipe2_nom = $stmt->fetchColumn();

        $message = "📢 Le match $equipe1_nom vs $equipe2_nom commence bientôt à $date_match !";

        // Insérer la notification
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, match_id, message, vue, date_notification) VALUES (?, ?, ?, 0, NOW())");
        $stmt->execute([$user_id, $match_id, $message]);

        // Marquer la notification comme envoyée pour éviter les doublons
        $stmt = $pdo->prepare("UPDATE abonnements SET notification_envoyee = 1 WHERE user_id = ? AND match_id = ?");
        $stmt->execute([$user_id, $match_id]);
    }

    echo "✅ Notifications mises à jour.";
} catch (PDOException $e) {
    die("❌ Erreur SQL : " . $e->getMessage());
}
?>
