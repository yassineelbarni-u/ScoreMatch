<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show text-center" role="alert">';
    echo $_SESSION['message']; // Afficher le message
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['message']); // Supprimer le message après l'affichage
}

// Récupérer tous les matchs depuis la base de données
$matchs = $pdo->query("
    SELECT m.id, e1.nom AS equipe1, e2.nom AS equipe2, e1.logo AS logo1, e2.logo AS logo2, 
           m.date_match AS match_date, 
           m.heure AS match_time
    FROM matches m
    JOIN equipes e1 ON m.equipe1_id = e1.id
    JOIN equipes e2 ON m.equipe2_id = e2.id
    ORDER BY m.date_match ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Matchs de Botola Pro Inwi</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../public/assets/css/calendrier.css">
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : '' ?>">


<!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>


<!-- Section des matchs -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4 text-center">Matchs de Botola Pro</h2>
        <div class="match-list">
            <?php foreach ($matchs as $match) : ?>
                
                <a href="match_details.php?id=<?= $match['id'] ?>" class="match-card-link">

                <div class="match-card" data-match-id="<?= $match['id'] ?>">

                    <!-- Date et Heure -->

                    <div class="match-info">
                        <?php
                            if (isset($match['match_date'])) {
                                $formatted_date = date('d/m/Y', strtotime($match['match_date']));
                            } else {
                                $formatted_date = "Date inconnue";
                            }

                            if (!empty($match['match_time']) && $match['match_time'] !== "00:00:00") {
                                $formatted_time = date('H:i', strtotime($match['match_time']));
                            } else {
                                $formatted_time = ''; // On ne l'affiche pas si c'est 00:00:00
                            }

                            // Affichage final
                            $display_date = $formatted_date . (!empty($formatted_time) ? " " . $formatted_time : '');
                        ?>
                        <span><?= $display_date ?></span>
                    </div>
                    <!-- Logos des équipes -->
                    <div class="match-content">
                        <div class="team">
                            <img src="<?= htmlspecialchars($match['logo1']) ?>" alt="Équipe 1">
                            <p class="team-name"><?= htmlspecialchars($match['equipe1']) ?></p>
                        </div>
                        <div class="match-vs">VS</div>
                        <div class="team">
                            <img src="<?= htmlspecialchars($match['logo2']) ?>" alt="Équipe 2">
                            <p class="team-name"><?= htmlspecialchars($match['equipe2']) ?></p>
                        </div>
                    </div>

                    <!-- 🔔 Ajouter le bouton S’abonner ici -->
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <button class="btn btn-primary follow-btn" data-match-id="<?= $match['id'] ?>" data-type="match">🔔 Suivre</button>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Pied de page -->
<footer class="bg-dark text-white text-center py-3 mt-auto">
    <p class="mb-0">&copy; 2025 Gestion des Matchs - Tous droits réservés.</p>
</footer>

<!-- Bootstrap JS -->
<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $(".follow-btn").click(function() {
            var match_id = $(this).data("match-id"); // Récupérer l'ID du match
            var user_id = <?php echo $_SESSION['user_id']; ?>; // ID de l'utilisateur connecté

            $.ajax({
                type: "POST",
                url: "abonnement.php", // Le fichier qui gère l'abonnement
                data: { user_id: user_id, match_id: match_id },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message); // Afficher un message de confirmation
                    } else {
                        alert(response.message); // Afficher un message d'erreur si besoin
                    }
                },
                error: function() {
                    alert("Erreur lors de l'abonnement.");
                }
            });
        });
    });
</script>

</body>
</html>
