<?php
session_start();
require_once '../config/database.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la page de connexion si non connecté
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("SELECT nom, email, role, date_inscription FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable.");
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <h2 class="text-center">👤 Mon Profil</h2>
    <div class="card shadow p-4">
        <table class="table table-bordered">
            <tr><th>Nom</th><td><?= htmlspecialchars($user['nom']) ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
            <tr><th>Rôle</th><td><?= htmlspecialchars($user['role']) ?></td></tr>
            <tr><th>Date d'inscription</th><td><?= htmlspecialchars($user['date_inscription']) ?></td></tr>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-secondary">Retour à l'accueil</a>
        <a href="logout.php" class="btn btn-danger">Déconnexion</a>
    </div>
</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
