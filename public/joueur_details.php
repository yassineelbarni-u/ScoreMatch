<?php
session_start();
require_once '../config/database.php'; // Connexion à la base de données

// Vérifier si un ID joueur est passé
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Aucun joueur spécifié.");
}

$joueur_id = intval($_GET['id']);

// Récupérer les détails du joueur avec le nom de son équipe
try {
    $stmt = $pdo->prepare("
        SELECT joueurs.*, equipes.nom AS equipe_nom
        FROM joueurs
        LEFT JOIN equipes ON joueurs.equipe_id = equipes.id
        WHERE joueurs.id = ?
    ");
    $stmt->execute([$joueur_id]);
    $joueur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$joueur) {
        die("Joueur introuvable.");
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
    <title>Profil du Joueur - <?= htmlspecialchars($joueur['nom']) ?></title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <h2 class="text-center">Profil de <?= htmlspecialchars($joueur['nom']) ?> <?= htmlspecialchars($joueur['prenom']) ?></h2>
    <div class="card shadow p-4">
        <table class="table table-bordered">
            <tr><th>Nom</th><td><?= htmlspecialchars($joueur['nom']) ?></td></tr>
            <tr><th>Prénom</th><td><?= htmlspecialchars($joueur['prenom']) ?></td></tr>
            <tr><th>Âge</th><td><?= htmlspecialchars($joueur['age']) ?></td></tr>
            <tr><th>Position</th><td><?= htmlspecialchars($joueur['position']) ?></td></tr>
            <tr><th>Équipe</th><td>
                <?php if (!empty($joueur['equipe_nom'])) : ?>
                    <a href="team_details.php?id=<?= $joueur['equipe_id'] ?>" class="text-decoration-none">
                        <?= htmlspecialchars($joueur['equipe_nom']) ?>
                    </a>
                <?php else : ?>
                    Aucun club
                <?php endif; ?>
            </td></tr>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="recherche.php" class="btn btn-secondary">Retour à la recherche</a>
    </div>
</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
