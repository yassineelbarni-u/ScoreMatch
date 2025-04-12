<?php
session_start();
require_once '../config/database.php'; // Connexion à la base de données

// Vérifier si une recherche a été effectuée
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupérer les équipes en fonction de la recherche
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT id, nom, logo FROM equipes WHERE nom LIKE ? ORDER BY nom");
        $stmt->execute(["%" . $search . "%"]);
    } else {
        $stmt = $pdo->query("SELECT id, nom, logo FROM equipes ORDER BY nom");
    }
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Équipes - Botola Pro Inwi</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../public/assets/css/teams.css">

</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : '' ?>">

  <!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>

<!-- Barre de navigation -->


<!-- Section des équipes -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4 text-center">Équipes de Botola Pro Inwi</h2>
        <!-- Barre de recherche -->
<form method="GET" class="mb-4">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Rechercher une équipe..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </div>
</form>

        <div class="row justify-content-center">

        
            <div class="col-md-6">
                <?php
                if (!empty($teams)) {
                    foreach ($teams as $team) {
                        echo '<div class="card mb-3 shadow">';
                        echo '<div class="row g-0 align-items-center">';
                        echo '<div class="col-md-4 text-center">';
                        echo '<img src="' . htmlspecialchars($team["logo"]) . '" class="img-fluid rounded-start p-3" alt="' . htmlspecialchars($team["nom"]) . '" style="max-width: 100px;">';
                        echo '</div>';
                        echo '<div class="col-md-8">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($team["nom"]) . '</h5>';
                        echo '<a href="team_details.php?id=' . $team["id"] . '" class="btn btn-primary">Voir Détails</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-center">Aucune équipe trouvée.</p>';
                }
                ?>
            </div>
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
            window.location.href = "teams.php"; // Recharge la page pour afficher toutes les équipes
        }
    });

    // Empêcher la soumission du formulaire si le champ est vide
    searchForm.addEventListener("submit", function (e) {
        if (searchInput.value.trim() === "") {
            e.preventDefault(); // Empêche l'envoi de la requête vide
            window.location.href = "teams.php"; // Recharge toutes les équipes
        }
    });
});
</script>

</body>
</html>
