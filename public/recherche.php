<?php
session_start();
require_once '../config/database.php'; // Connexion à la base de données

// Vérifier si une recherche a été effectuée
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Requête SQL pour récupérer les joueurs et les équipes selon la recherche
try {
    // Recherche dans les joueurs
    if (!empty($search)) {
        $stmtJoueurs = $pdo->prepare("SELECT id, nom, prenom FROM joueurs WHERE nom LIKE ? OR prenom LIKE ? ORDER BY nom");
        $stmtJoueurs->execute(["%" . $search . "%", "%" . $search . "%"]);
    } else {
        $stmtJoueurs = $pdo->query("SELECT id, nom, prenom FROM joueurs ORDER BY nom");
    }
    $joueurs = $stmtJoueurs->fetchAll(PDO::FETCH_ASSOC);

    // Recherche dans les équipes
    if (!empty($search)) {
        $stmtEquipes = $pdo->prepare("SELECT id, nom FROM equipes WHERE nom LIKE ? ORDER BY nom");
        $stmtEquipes->execute(["%" . $search . "%"]);
    } else {
        $stmtEquipes = $pdo->query("SELECT id, nom FROM equipes ORDER BY nom");
    }
    $equipes = $stmtEquipes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recherche</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">

</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : '' ?>">

<!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>

<!-- Section de recherche -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4 text-center">Rechercher Joueurs & Équipes</h2>

        <!-- Barre de recherche -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un joueur ou une équipe..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </div>
        </form>

        <div class="row">
            <!-- Liste des joueurs -->
            <div class="col-md-6">
    <h4 class="text-center">Joueurs</h4>
    <ul class="list-group">
        <?php
        if (!empty($joueurs)) {
            foreach ($joueurs as $joueur) {
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                echo '<a href="joueur_details.php?id=' . $joueur["id"] . '" class="text-decoration-none">' . htmlspecialchars($joueur["nom"]) . ' ' . htmlspecialchars($joueur["prenom"]) . '</a>';
                echo '<button class="btn btn-primary btn-sm follow-btn" data-id="' . $joueur["id"] . '" data-type="joueur">Suivre</button>';
                echo '</li>';
            }
        } else {
            echo '<li class="list-group-item text-center">Aucun joueur trouvé.</li>';
        }
        ?>
    </ul>
</div>


            <!-- Liste des équipes -->
            <div class="col-md-6">
    <h4 class="text-center">Équipes</h4>
    <ul class="list-group">
        <?php
        if (!empty($equipes)) {
            foreach ($equipes as $equipe) {
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                echo '<a href="team_details.php?id=' . $equipe["id"] . '" class="text-decoration-none">' . htmlspecialchars($equipe["nom"]) . '</a>';
                echo '<button class="btn btn-secondary btn-sm follow-btn" data-id="' . $equipe["id"] . '" data-type="equipe">Suivre</button>';
                echo '</li>';
            }
        } else {
            echo '<li class="list-group-item text-center">Aucune équipe trouvée.</li>';
        }
        ?>
    </ul>
</div>

        </div>
    </div>
</section>



<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

$(document).ready(function() {
    $(".follow-btn").click(function() {
        var btn = $(this);
        var type = btn.data('type');
        var id = btn.data('id');

        $.ajax({
            type: "POST",
            url: "ajouter_abonnement.php",
            data: { 
                type: type, 
                id: id 
            },
            dataType: "json",
            success: function(response) {
                Swal.fire({
                    icon: response.status,
                    title: response.status.toUpperCase(),
                    text: response.message,
                    confirmButtonColor: '#3085d6',
                });

                if (response.status === 'success') {
                    btn.prop('disabled', true)
                       .removeClass('btn-primary btn-secondary')
                       .addClass('btn-success')
                       .html('✓ Abonné');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur technique : ' + xhr.responseText
                });
            }
        });
    });
});


</script>
</body>
</html>


</body>
</html>
