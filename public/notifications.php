<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p class='alert alert-warning text-center'>⚠️ Vous devez être connecté pour voir vos notifications.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
    SELECT n.id, n.message, n.date_notification, n.match_id,
           m.equipe1_id, m.equipe2_id,
           e1.nom AS equipe1, e2.nom AS equipe2
    FROM notifications n
    LEFT JOIN matches m ON n.match_id = m.id
    LEFT JOIN equipes e1 ON m.equipe1_id = e1.id
    LEFT JOIN equipes e2 ON m.equipe2_id = e2.id
    WHERE n.user_id = ?
    ORDER BY n.date_notification DESC
");
    
// Vérifier que $user_id est bien défini
if (!isset($user_id) || empty($user_id)) {
    die("<p class='alert alert-danger text-center'>❌ Erreur : ID utilisateur manquant</p>");
}

// Exécution correcte
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<p class='alert alert-danger text-center'>❌ Erreur SQL : " . $e->getMessage() . "</p>");
}





?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes Notifications</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <h2 class="text-center">🔔 Mes Notifications</h2>

    <?php if (empty($notifications)) : ?>
        <p class="alert alert-info text-center">📭 Aucune nouvelle notification.</p>
    <?php else : ?>
        <ul class="list-group">


        <?php foreach ($notifications as $notif) : ?>
    <?php 
        $equipe1 = !empty($notif['equipe1']) ? htmlspecialchars($notif['equipe1']) : "Équipe inconnue";
        $equipe2 = !empty($notif['equipe2']) ? htmlspecialchars($notif['equipe2']) : "Équipe inconnue";
    ?>
    <li id="notif-<?= $notif['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center <?= isset($notif['vue']) && $notif['vue'] == 0 ? 'fw-bold' : '' ?>">
        <a href="match_details.php?match_id=<?= htmlspecialchars($notif['match_id']) ?>" class="text-decoration-none text-dark">
            📢 <?= $equipe1 . " vs " . $equipe2 ?>
        </a>
        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notif['date_notification'])) ?></small>
    </li>
<?php endforeach; ?>


    


</ul>


    <?php endif; ?>
</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>
</body>
</html>
