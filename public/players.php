<?php
session_start();
require_once '../config/database.php'; // Connexion à la base de données

// Vérifier si une recherche a été effectuée
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupérer les joueurs en fonction de la recherche
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, age, position, equipe_id FROM joueurs WHERE nom LIKE ? OR prenom LIKE ? ORDER BY nom");
        $stmt->execute(["%" . $search . "%", "%" . $search . "%"]);
    } else {
        $stmt = $pdo->query("SELECT id, nom, prenom, age, position, equipe_id FROM joueurs ORDER BY nom");
    }
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Joueurs</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : '' ?>">

<!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>

<!-- Section des joueurs -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4 text-center">Liste des Joueurs</h2>

        <!-- Barre de recherche -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un joueur..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Âge</th>
                        <th>Position</th>
                        <th>ID Équipe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($players)) {
                        foreach ($players as $player) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($player['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($player['nom']) . "</td>";
                            echo "<td>" . htmlspecialchars($player['prenom']) . "</td>";
                            echo "<td>" . htmlspecialchars($player['age']) . "</td>";
                            echo "<td>" . htmlspecialchars($player['position']) . "</td>";
                            echo "<td>" . htmlspecialchars($player['equipe_id']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center">Aucun joueur trouvé.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    let searchInput = document.querySelector("input[name='search']");
    let searchForm = document.querySelector("form");

    // Détecter quand le champ de recherche est vidé
    searchInput.addEventListener("input", function () {
        if (searchInput.value.trim() === "") {
            window.location.href = "players.php"; // Recharge la page pour afficher tous les joueurs
        }
    });

    // Empêcher la soumission du formulaire si le champ est vide
    searchForm.addEventListener("submit", function (e) {
        if (searchInput.value.trim() === "") {
            e.preventDefault(); // Empêche l'envoi de la requête vide
            window.location.href = "players.php"; // Recharge tous les joueurs
        }
    });
});
</script>

</body>
</html>
