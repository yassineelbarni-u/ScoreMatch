<?php
session_start();
require_once '../config/database.php';

// Vérifiez si le tournoi_id est 2 pour Kass L3arch
if (isset($_GET['tournoi_id']) && $_GET['tournoi_id'] == 2) {
    // C'est un match du tournoi Kass L3arch, on peut charger les matchs ici
    $tournoi_id = 2;
} else {
    // Si ce n'est pas Kass L3arch, on redirige ou on affiche une erreur
    echo "Vous essayez d'accéder à un tournoi incorrect.";
    exit(); // Ou rediriger vers une page d'erreur
}

// Récupérer les matchs du tournoi Kass L3arch
$query = "SELECT m.*, 
                  e1.nom AS equipe1, e2.nom AS equipe2, 
                  e1.logo AS logo1, e2.logo AS logo2,
                  m.score_equipe1, m.score_equipe2,
                  DATE(m.date_match) AS match_date
           FROM matches m
           JOIN equipes e1 ON m.equipe1_id = e1.id
           JOIN equipes e2 ON m.equipe2_id = e2.id
           WHERE m.tournoi_id = :tournoi_id
           AND e1.elimine = 0 AND e2.elimine = 0";  // Exclure les équipes éliminées

$stmt = $pdo->prepare($query);
$stmt->bindParam(':tournoi_id', $tournoi_id);
$stmt->execute();
$matchs_resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats des Matchs - Kass L3arch</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../public/assets/css/resultats.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <h2 class="text-center">Historique des Résultats - Kass L3arch</h2>
    
    <!-- Sélection de la date -->
    <form method="GET" id="dateFilterForm" class="text-center mb-3">
        <label for="filter_date" class="fw-bold">Sélectionner une date :</label>
        <input type="date" id="filter_date" name="date" class="form-control d-inline-block w-auto" value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>">
        <button type="submit" class="btn btn-primary"> Rechercher</button>
    </form>

    <div class="match-list">
        <?php
        if (empty($matchs_resultats)) {
            echo "<p class='text-center text-muted'>Aucun match trouvé pour cette date.</p>";
        }

        $last_date = null;

        foreach ($matchs_resultats as $match) {
            $current_date = date('d/m/Y', strtotime($match['match_date']));

            // Affichage de la date comme titre si elle change
            if ($current_date !== $last_date) {
                echo "<div class='date-separator'>📅 " . $current_date . "</div>";
                $last_date = $current_date;
            }
            ?>
            <div class="match-item" onclick="goToDetails(<?= $match['id'] ?>)">
                <div class="match-info">
                    <span class="match-date">
                        <?= substr($match['heure'], 0, 5); ?>
                    </span>
                    <span class="match-tour">
                        <!-- Affichage du tour (ex: Quart de Final, Demi Final, etc.) -->
                        <?= htmlspecialchars($match['tour']) ?>
                    </span>
                </div>
                <div class="match-content">
                    <div class="team">
                        <img src="<?= htmlspecialchars($match['logo1']) ?>" alt="Équipe 1">
                    </div>
                    <div class="match-score">
                        <?php if ($match['score_equipe1'] !== null && $match['score_equipe2'] !== null): ?>
                            <?= htmlspecialchars($match['score_equipe1']) ?> - <?= htmlspecialchars($match['score_equipe2']) ?>
                        <?php else: ?>
                            <span class="text-muted">Match en attente</span>
                        <?php endif; ?>
                    </div>
                    <div class="team">
                        <img src="<?= htmlspecialchars($match['logo2']) ?>" alt="Équipe 2">
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<script>
function goToDetails(matchId) {
    window.location.href = "detail_resultat.php?match_id=" + matchId;
}
</script>

</body>
</html>
