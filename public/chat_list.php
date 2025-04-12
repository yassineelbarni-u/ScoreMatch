<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer tous les utilisateurs avec le rôle 'user', sauf l'utilisateur connecté
$stmt = $pdo->prepare("SELECT id, nom FROM users WHERE id != ? AND role = 'user'");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir un utilisateur pour discuter</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2 class="text-center my-4">Choisissez un utilisateur pour discuter</h2>

        <div class="list-group">
            <?php foreach ($users as $user) : ?>
                <a href="chat.php?receiver_id=<?= $user['id'] ?>" class="list-group-item list-group-item-action">
                    <?= htmlspecialchars($user['nom']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>
